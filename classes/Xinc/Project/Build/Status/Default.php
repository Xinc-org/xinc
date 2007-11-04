<?php
/**
 * PUT DESCRIPTION HERE
 * 
 * @package Xinc
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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

require_once 'Xinc/Project/Build/Status/Interface.php';

class Xinc_Project_Build_Status_Default implements Xinc_Project_Build_Status_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Project
     */
    private $_project;
    private $_properties = array();
    private $_labels = array();
    private $_buildtime;
    private $_lastSuccessfulBuildTime;
    private $_status=1;
    
    public function setProject(Xinc_Project &$project)
    {
        $this->_project=$project;
        $this->unserialize();
    }
    public function unserialize()
    {
        $dir=Xinc::getInstance()->getStatusDir();
        $filename = $dir . DIRECTORY_SEPARATOR . $this->_project->getName() . DIRECTORY_SEPARATOR. 'status.ser';
        $this->_project->debug('unserialize status');
        if (file_exists($filename)) {
            $statusData = parse_ini_file($filename, true);
            foreach ($statusData as $key => $value) {
                if(substr($key, 0, 6)== 'build.') $this->setProperty('last'.$key, $value);
                if(substr($key, 0, 7)== 'sticky.') $this->setProperty($key, $value);
            }
            $lastBuildTime = $statusData['build.time'];
            $this->setBuildTime($lastBuildTime);
            if (isset($statusData['lastsuccessfulbuild'])) {
                $this->_lastSuccessfulBuildTime= $statusData['lastsuccessfulbuild'];
            }
        }
        else
        {
            
        }
        //var_dump($this->_properties);
    }
    
    public function serialize()
    {
       
        $dir=Xinc::getInstance()->getStatusDir();
        $isSuccessful = $this->getStatus() == self::PASSED;
        $buildStatusData = array();
        $buildStatusData['project.name'] = $this->_project->getName();
        $buildStatusData['build.successful'] = $isSuccessful ? 1:0;
        $buildStatusData['build.status'] = $this->getStatus();
        $buildStatusData['build.time'] = $this->getBuildTime();
        $buildStatusData['lastsuccessfulbuild'] = $isSuccessful ? $this->getBuildTime() :
                                                                  $this->getLastSuccessfulBuildTime();
        /**
         * Merge the historical properties with the new ones
         * BEWARE, the new ones (like lastbuild.status) have to be overwritten
         * with the new values
         */
        $buildStatusData = array_merge($this->_properties, $buildStatusData);
        $buildStatusData['labels'] = $this->getBuildLabels();
        $filename = $dir . DIRECTORY_SEPARATOR . $this->_project->getName() . DIRECTORY_SEPARATOR . 'status.ser';
        $statusdir = dirname($filename);
        if (!file_exists($statusdir)) {
            
            $res = mkdir($statusdir, 0755, true);
            if (!$res) {
                $this->_project->error('Could not create ' 
                                      . 'status directory '
                                      . ' "'.$statusdir.'"');
                return false;
            }
        }
        
        $serializeResult = $this->_writeIniFile($filename, $buildStatusData);
        
        if ($serializeResult) { 
            $this->_project->debug('successfully serialized');
            $this->_serializeHistory($buildStatusData, $statusdir);
        } else {
            
            $this->_project->error('serialization error');
                                             
        }
        
        return $serializeResult;
    }
    
    private function _serializeHistory($data, $dir)
    {
        /**
         * Put the history data into subfolders,
         * otherwise the number of folders in the status-directory
         * would increase too fast
         */
        $month = date('m', $this->getBuildTime());
        $year = date('Y', $this->getBuildTime());
        $day = date('d', $this->getBuildTime());
        $path = $year . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $day . DIRECTORY_SEPARATOR . $this->getBuildTime();
        $historyPath = $dir . DIRECTORY_SEPARATOR . $path;
        $historyTrack = $dir. DIRECTORY_SEPARATOR . '.buildHistory';
        $fh=fopen($historyTrack, 'a');
        if ($fh) {
            fputs($fh, $historyPath."\n");
            fclose($fh);
        }
        $filename = $dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR .'status.ser';
        $logfile = $dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'buildlog.xml';
        if (!file_exists($filename)) {
            $statusdir = dirname($filename);
            $res = mkdir($statusdir, 0755, true);
            if (!$res) {
                $this->_project->error('Could not create ' 
                                      . 'status directory '
                                      . ' "'.$statusdir.'"');
                return false;
            }
        }
        $serializeResult = $this->_writeIniFile($filename, $data);
        if ($serializeResult) { 
            $this->_project->debug('successfully serialized history');
            
        } else {
            
            $this->_project->error('history serialization error');
                                             
        }
        Xinc_Logger::getInstance()->setBuildLogFile($logfile);
        Xinc_Logger::getInstance()->flush();
    }
    
    public function setProperty($name, $value)
    {
       
       $this->_properties[$name] = $value;
       
    }
    
    public function getProperty($name)
    {
        
        return isset($this->_properties[$name]) ? $this->_properties[$name] : null;
       
    }
    
    public function setBuildTime($timestamp)
    {
        Xinc_Logger::getInstance()->debug('Setting build time '.$timestamp);
        $this->_buildtime = $timestamp;
    }
    
    public function getBuildTime()
    {
        return $this->_buildtime;
    }
    public function getLastBuildStatus()
    {
        return $this->getProperty('lastbuild.status');
    }
    
    public function getLastSuccessfulBuildTime()
    {
        return $this->_lastSuccessfulBuildTime;
    }
    
    public function addBuildLabel($label)
    {
        $this->_labels[$label] = 1;
    }
    
    public function getBuildLabels()
    {
        return array_keys($this->_labels);
    }
    public function setStatus($status)
    {
        $this->_status=$status;
    }
    public function getStatus()
    {
        return $this->_status;
    }
    private function _writeIniFile($filename,$data)
    {
   
        $content = '';

        foreach ($data as $key=>$elem) {
            if (is_array($elem)) {
                if ($key != '') {
                    $content .= '['.$key."]\r\n";                   
                }
               
                foreach ($elem as $key2=>$elem2) {
                   
                        $content .= $key2.' = '.$elem2."\r\n";
                    
                }
            } else {
                $content .= $key.' = '.$elem."\r\n";
            }
        }

        if (!$handle = fopen($filename, 'w')) {
            return false;
        }
        if (!fwrite($handle, $content)) {
            return false;
        }
        fclose($handle);
        return true;
    
    }
    public function buildSuccessful()
    {
        $this->serialize();
    }
    public function buildFailed()
    {
        $this->serialize();
    }
}