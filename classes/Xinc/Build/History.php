<?php
/**
 * Build History retrieves the buildtimes of a project
 * 
 * @package Xinc.Build
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once 'Xinc/Project.php';
require_once 'Xinc/Build/History/Exception/Storage.php';
require_once 'Xinc/Build/History/Exception/Migration.php';
require_once 'Xinc/Build/History/Exception/General.php';
require_once 'Xinc/Build/History/Exception/MetaFile.php';
require_once 'Xinc/Build/Exception/Unserialization.php';

class Xinc_Build_History
{
    const PART_MAX = 1000;
    const HISTORY_DIR = '.history';
    
    /**
     * Get the number of historical builds for a project
     *
     * @param Xinc_Project $project
     * @return integer
     * @throws Xinc_Build_History_Exception_General
     */
    public static function getCount(Xinc_Project &$project)
    {
        $metaData = self::_loadMetaData($project->getName());
        
        if (!isset($metaData['meta'])) {
            self::_migrate($project->getName(), $metaData);
            return self::getCount($project);
        } else {
            $total = 0;
            foreach ($metaData['parts'] as $part) {
                $total += $part['count'];
            }
            return $total;
        }
    }
    
    /**
     * Returns an array of historical builds
     *
     * @param Xinc_Project $project
     * @param integer $start
     * @param integer $limit
     * @return array
     * @throws Xinc_Build_History_Exception_General
     */
    public static function getFromTo(Xinc_Project &$project, $start, $limit)
    {
        /**
         * reverse, go from back to front! start by last part not FIRST
         */
        $metaData = self::_loadMetaData($project->getName());
        if (!isset($metaData['meta'])) {
            self::_migrate($project->getName(), $metaData);
            return self::getFromTo($project, $start, $limit);
        } else {
            $startNo = 0;
            $totalStart = 0;
            for ($i = count($metaData['parts']) - 1; $i>=0; $i--) {
                $part = $metaData['parts'][$i];
                if ($totalStart > $start) {
                    // we found our start box
                    break;
                }
                $totalStart += $part['count'];
                $startNo = $part['no'];
            }

            $endNo = 0;
            $totalEnd = 0;
            for ($i = $startNo; $i>=0; $i--) {
                $part = $metaData['parts'][$i];
                if ($totalEnd > $limit) {
                    $endNo = $part['no'];
                    break;
                }
                $totalEnd += $part['count'];
            }

            $arr = null;

            for ($i=$endNo; $i<=$startNo; $i++) {
                $partArr = self::_readPartFile($project->getName(), $i);
                $partArr = array_reverse($partArr, true);
                if ($arr == null) {
                    $arr = $partArr;
                } else {
                    foreach ($partArr as $key => $val) {
                        $arr[$key] = $val;
                    }
                }
            }

            $startSlice = $start - $totalStart;
            if ($limit == null) {
                $limit = count($arr) - $startSlice;
            }
            $needed = array_slice($arr, $start - $totalStart, $limit, true);
            krsort($needed);
            return $needed;
        }
    }
    
    /**
     * returns an array of build timestamps for a project
     *
     * @param Xinc_Project $project
     * @return array
     * @throws Xinc_Build_Exception_Unserialization
     */
    public static function get(Xinc_Project &$project)
    {
        $projectName = $project->getName();
        
        $statusDir = self::_getStatusDir();
        
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        if (file_exists($historyFile)) {
            $buildHistoryArr = @unserialize(file_get_contents($historyFile));
            if ($buildHistoryArr === false) {
                throw new Xinc_Build_Exception_Unserialization($project, null);
            } else if (isset($buildHistoryArr['meta'])) {
                $buildHistoryArr = array();
                /**
                 * new format, we need to load from multiple places
                 */
                foreach ($buildHistoryArr['parts'] as $part) {
                    $fileName = $part['filename'];
                    $buildHistoryArr = array_merge(@unserialize(file_get_contents($fileName)), $buildHistoryArr);
                }
                
            } else {
                self::_migrate($projectName, $buildHistoryArr);
                return self::get($project);
            }
        } else {
            $buildHistoryArr = array();
        }
        
        return $buildHistoryArr;
    }
    /**
     * Gets the last build filename, of the serialized build
     *
     * @param Xinc_Project $project
     * @return string
     * @throws Xinc_Build_Exception_NotFound
     * @throws Xinc_Build_Exception_Unserialization
     * @throws Xinc_Build_Exception_NotFound
     * @throws Xinc_Build_History_Exception_General
     */
    public static function getLastBuildFile(Xinc_Project &$project)
    {
        $lastBuildTimestamp = self::getLastBuildTime($project);
        return self::getBuildFile($project, $lastBuildTimestamp);
    }
    
    public static function getLastSuccessfulBuildFile(Xinc_Project &$project)
    {
        $lastBuildTimestamp = self::getLastSuccessfulBuildTime($project);
        return self::getBuildFile($project, $lastBuildTimestamp);
    }
    /**
     * Returns the directory of the serialized build
     *
     * @param Xinc_Project $project
     * @param integer $timestamp
     * @return string
     * @throws Xinc_Build_Exception_NotFound
     * @throws Xinc_Build_Exception_NotFound
     * @throws Xinc_Build_History_Exception_General
     */
    public static function getBuildDir(Xinc_Project &$project, $timestamp)
    {
        $buildFile = self::getBuildFile($project, $timestamp);
        return dirname($buildFile);
    }
    
    /**
     * Gets the filename of a historical build
     *
     * @param Xinc_Project $project
     * @param integer $timestamp
     * @return string
     * @throws Xinc_Build_Exception_NotFound
     * @throws Xinc_Build_History_Exception_General
     */
    public static function getBuildFile(Xinc_Project &$project, $timestamp)
    {
        $metaFileArr = self::_loadMetaData($project->getName());
        if ($metaFileArr === false) {
            throw new Xinc_Build_Exception_NotFound($project, $timestamp);
            //return null;
        } else if (!isset($metaFileArr['meta'])) {
            self::_migrate($project->getName(), $metaFileArr);
            return self::getBuildFile($project, $timestamp);
        } else {
            foreach ($metaFileArr['parts'] as $part) {
                if ($timestamp >= $part['from'] && $timestamp <= $part['to']) {
                    $partFile = self::_readPartFile($project->getName(), $part['no']);
                    if (isset($partFile[$timestamp])) {
                        return $partFile[$timestamp];
                    } else {
                        throw new Xinc_Build_Exception_NotFound($project, $timestamp);
                        //return null;
                    }
                }
            }
        }
        
    }
    
    /**
     *
     * @param Xinc_Project $project
     * @return unknown
     * @throws Xinc_Build_Exception_NotFound
     * @throws Xinc_Build_History_Exception_General
     */
    public static function getLastBuildDir(Xinc_Project &$project)
    {
        $buildFile = self::getLastBuildFile($project);
        return dirname($buildFile);
    }
    /**
     * Finds the last build time of a project
     *
     * @param Xinc_Project $project
     * @return integer
     * @throws Xinc_Build_Exception_Unserialization
     */
    public static function getLastBuildTime(Xinc_Project &$project)
    {
        $projectName = $project->getName();
        
        $statusDir = self::_getStatusDir();
        
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        if (file_exists($historyFile)) {
            $buildHistoryArr = @unserialize(file_get_contents($historyFile));
            if ($buildHistoryArr === false) {
                throw new Xinc_Build_Exception_Unserialization($project, null);
            } else if (isset($buildHistoryArr['meta'])) {
                //$buildHistoryArr = array();
                /**
                 * new format, we need to load from multiple places
                 */
                $count = count($buildHistoryArr['parts'])-1;
                if ($count>=0) {
                    $lastPart = $buildHistoryArr['parts'][$count];
                    $fileName = $lastPart['filename'];
                    $buildHistoryArr = @unserialize(file_get_contents($fileName));
                    if ($buildHistoryArr === false) {
                        throw new Xinc_Build_Exception_Unserialization($project, null);
                    }
                    $keys = array_keys($buildHistoryArr);
                    $lastTimestamp = $keys[count($keys)-1];
                } else {
                    return null;
                }
                
            } else {
                self::_migrate($projectName, $buildHistoryArr);
                return self::getLastBuildTime($project);
                /**$keys = array_keys($buildHistoryArr);
                $lastTimestamp = $buildHistoryArr[$keys[count($keys)-1]];*/
            }
        } else {
            $lastTimestamp = null;
        }
        
        return $lastTimestamp;
    }
    
    public static function getLastSuccessfulBuildTime(Xinc_Project &$project)
    {
        $projectName = $project->getName();
        
        $statusDir = self::_getStatusDir();
        
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        if (file_exists($historyFile)) {
            $buildHistoryArr = @unserialize(file_get_contents($historyFile));
            if ($buildHistoryArr === false) {
                throw new Xinc_Build_Exception_Unserialization($project, null);
            } else if (isset($buildHistoryArr['meta'])) {
                //$metaFileArr['lastSuccessfulBuild']
                if (isset($buildHistoryArr['lastSuccessfulBuild'])) {
                    return $buildHistoryArr['lastSuccessfulBuild']['buildtime'];
                } else {
                    $project->error('Cannot get last successful build. Using last build instead');
                    return self::getLastBuildTime($project);
                }
                
            } else {
                self::_migrate($projectName, $buildHistoryArr);
                return self::getLastBuildTime($project);
                /**$keys = array_keys($buildHistoryArr);
                $lastTimestamp = $buildHistoryArr[$keys[count($keys)-1]];*/
            }
        } else {
            $lastTimestamp = null;
        }
        
        return $lastTimestamp;
    }
    /**
     * returns the status dir
     *
     * @return string
     */
    private static function _getStatusDir()
    {
        $statusDir = null;
        $isGuiMode = false;
        $isXincMode = false;
        if (class_exists('Xinc_Gui_Handler')) {
            $handler = Xinc_Gui_Handler::getInstance();
            $isGuiMode = $handler instanceof Xinc_Gui_Handler;
        }
        if (class_exists('Xinc')) {
            $xinc = Xinc::getInstance();
            $isXincMode = $xinc instanceof Xinc;
        }
        if ($isGuiMode) {
            // we are in gui mode
            $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        } else if ($isXincMode) {
            $statusDir = Xinc::getInstance()->getStatusDir();
        } else {
            $statusDir = getcwd();
        }
        return $statusDir;
    }
    
    /**
     * Adds a build to the history file
     *
     * @param Xinc_Build_Interface $build
     * @param string $serialFileName
     * @throws Xinc_Build_History_Exception_General
     */
    public static function addBuild(Xinc_Build_Interface &$build, $serialFileName)
    {
        $buildSuccess = $build->getStatus() == Xinc_Build_Interface::PASSED;
        $project = $build->getProject();
        $metaFileArr = self::_loadMetaData($project->getName());
        
        if (!isset($metaFileArr['meta'])) {
            
            self::_migrate($project->getName(), $metaFileArr);
            $metaFileArr = self::_loadMetaData($project->getName());
        }
        if (count($metaFileArr['parts'])>0) {
            $lastNo = count($metaFileArr['parts'])-1;
            $lastPart = $metaFileArr['parts'][$lastNo];
            $count = $lastPart['count'];
            if ($count >= self::PART_MAX) {
                $arr = array();
                $arr[$build->getBuildTime()] = $serialFileName;
                try {
                    $partFile = self::_writePartFile($project->getName(), $lastNo+1, $arr);
                } catch (Xinc_Build_History_Exception_Storage $e1) {
                    Xinc_Logger::getInstance()->error('Cannot write build history file for '. $project->getName());
                }
                $metaFileArr['parts'][] = array('no' => $lastNo+1,
                                                'count'=>1,
                                                'filename'=>$partFile,
                                                'from'=>$build->getBuildTime(),
                                                'to'=>$build->getBuildTime());
                
            } else {
                $arr = self::_readPartFile($project->getName(), $lastNo);
                $arr[$build->getBuildTime()] = $serialFileName;
                $metaFileArr['parts'][$lastNo]['count']++;
                $metaFileArr['parts'][$lastNo]['to'] = $build->getBuildTime();
                try {
                    self::_writePartFile($project->getName(), $lastNo, $arr);
                } catch (Xinc_Build_History_Exception_Storage $e1) {
                    Xinc_Logger::getInstance()->error('Cannot write build history file for '. $project->getName());
                }
            }
        } else {
            $arr = array();
            $arr[$build->getBuildTime()] = $serialFileName;
            try {
                $partFile = self::_writePartFile($project->getName(), 0, $arr);
            } catch (Xinc_Build_History_Exception_Storage $e1) {
                Xinc_Logger::getInstance()->error('Cannot write build history file for '. $project->getName());
                $partFile = null;
            }
            $metaFileArr['parts'][] = array('no' => 0,
                                            'count'=>1,
                                            'filename'=>$partFile,
                                            'from'=>$build->getBuildTime(),
                                            'to'=>$build->getBuildTime());
        }
        try {
            $metaFileArr['lastSuccessfulBuild'] = array('filename' => $serialFileName,
                                                        'buildtime' => $build->getBuildTime());
            self::_writeMetaData($project->getName(), $metaFileArr);
        } catch (Exception $e) {
            Xinc_Logger::getInstance()->error($e->getMessage() . '; Project: ' . $project->getName());
        }
    }
    
    /**
     * @param string $projectName
     * @return array
     * @throws Xinc_Build_History_Exception_General
     */
    private static function _loadMetaData($projectName)
    {
        $projectName = trim($projectName);
        if (empty($projectName)) {
            throw new Xinc_Build_History_Exception_General('Must provide a project name');
        }
        $metaFileName = self::_getMetaFileName($projectName);
        if (file_exists($metaFileName)) {
            $metaFileArr = @unserialize(file_get_contents($metaFileName));
            if (!is_array($metaFileArr)) {
                $metaFileArr = array('meta'=>true, 'parts'=>array());
            }
        } else {
            $metaFileArr = array('meta'=>true, 'parts'=>array());
        }
        return $metaFileArr;
    }
    
    /**
     *
     * @param string $projectName
     * @return string
     */
    private static function _getMetaFileName($projectName)
    {
        $statusDir = self::_getStatusDir();
        $metaFileName = $statusDir;
        $metaFileName .= DIRECTORY_SEPARATOR;
        $metaFileName .= $projectName . '.history';
        return $metaFileName;
    }
    
    /**
     * Migrates old history files ( which had all data in one file)
     * to new history files (which use one meta file, and several
     * smaller data files)
     *
     * @param string $projectName
     * @param array $arr
     * @throws Xinc_Build_History_Exception_MetaFile
     * @throws Xinc_Build_History_Exception_Migration
     */
    private static function _migrate($projectName, array $arr)
    {
        if (empty($projectName)) {
            throw new Xinc_Build_History_Exception_Migration();
        }
        $metaFileName = self::_getMetaFileName($projectName);
        if (!is_readable($metaFileName)) {
            throw new Xinc_Build_History_Exception_MetaFile('MetaFile "' . $metaFileName
                                                           . '" is not readable');
        }
        copy($metaFileName, $metaFileName . '.backup');
        $counter = 0;
        $fileNo = 0;
        $part = array();
        $i = 0;
        $totalCount = count($arr);
        $metaArr = array('meta'=>true, 'parts'=>array());
        foreach ($arr as $timestamp => $fileName) {
            $part[$timestamp] = $fileName;
            $i++;
            if (++$counter>=self::PART_MAX || $i>= $totalCount) {
                $no = $fileNo;
                try {
                    $statusFile = self::_writePartFile($projectName, $fileNo++, $part);
                } catch (Xinc_Build_History_Exception_Storage $e1) {
                    Xinc_Logger::getInstance()->error('Cannot write build history file for '. $project->getName());
                    $statusFile = null;
                }
                $keys = array_keys($part);
                $metaArr['parts'][] = array('filename'=>$statusFile,
                                            'from'=> $keys[0],
                                            'to'=>$keys[count($keys)-1],
                                            'count' => count($keys),
                                            'no' => $no );
                $part = array();
                $counter = 0;
            }
            
        }
        try {
            self::_writeMetaData($projectName, $metaArr);
        } catch (Exception $e) {
            Xinc_Logger::getInstance()->error($e->getMessage());
        }
        
    }
    
    /**
     *
     * @param string $projectName
     * @param array $arr
     * @return boolean
     * @throws Xinc_Build_History_Exception_MetaFile
     */
    private static function _writeMetaData($projectName, array $arr)
    {
        $metaFileName = self::_getMetaFileName($projectName);
        if (!is_writable($metaFileName) && !is_writable(dirname($metaFileName))) {
            throw new Xinc_Build_History_Exception_MetaFile('MetaFile "' 
                                                           . $metaFileName . '" is not writable');
        }
        $metaData = serialize($arr);
        $written = file_put_contents($metaFileName, $metaData);
        if ($written != strlen($metaData)) {
            throw new Xinc_Build_History_Exception_MetaFile('Failed writing into MetaFile "' 
                                                           . $metaFileName . '"');
        } else {
            return true;
        }
    }
    
    /**
     * @param string $projectName
     * @param integer $no
     * @param array $arr
     * @return string
     * @throws Xinc_Build_History_Exception_Storage
     */
    private static function _writePartFile($projectName, $no, array $arr)
    {
        $statusFile = self::_getStatusDir();
        $statusFile .= DIRECTORY_SEPARATOR;
        $statusFile .= $projectName;
        $statusFile .= DIRECTORY_SEPARATOR;
        $statusFile .= self::HISTORY_DIR;
        $statusFile .= DIRECTORY_SEPARATOR;
        $statusFile .= $no . '.history';
        if (!file_exists($statusFile)) {
            $dirCreated = mkdir(dirname($statusFile), 0755, true);
            if (!$dirCreated) {
                throw new Xinc_Build_History_Exception_Storage();
            }
        } else if (!is_writable($statusFile)) {
            throw new Xinc_Build_History_Exception_Storage();
        }
        $partFileData = serialize($arr);
        $written = file_put_contents($statusFile, $partFileData);
        if ($written == strlen($partFileData)) {
            return $statusFile;
        } else {
            throw new Xinc_Build_History_Exception_Storage();
        }
    }
    
    /**
     *
     * @param string $projectName
     * @param integer $no
     * @return array
     * @throws Xinc_Build_History_Exception_General
     */
    private static function _readPartFile($projectName, $no)
    {
        $statusFile = self::_getStatusDir();
        $statusFile .= DIRECTORY_SEPARATOR;
        $statusFile .= $projectName;
        $statusFile .= DIRECTORY_SEPARATOR;
        $statusFile .= self::HISTORY_DIR;
        $statusFile .= DIRECTORY_SEPARATOR;
        $statusFile .= $no . '.history';
        if (file_exists($statusFile)) {
            $contents = file_get_contents($statusFile);
            $arr = @unserialize($contents);
            if ($arr === false) {
                throw new Xinc_Build_History_Exception_General('Could not read part-file: ' . $statusFile);
            }
        } else {
            throw new Xinc_Build_History_Exception_General('Could not read part-file: ' . $statusFile);
        }
        return $arr;
    }
}
