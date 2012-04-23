<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet
 * @author    Arno Schneider <username@example.org>
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Exception/ModificationSet.php';
require_once 'Xinc/Ini.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Result.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Svn/Task.php';

class Xinc_Plugin_Repos_ModificationSet_Svn
    extends Xinc_Plugin_Base
{
    /**
     * @var string Path to project.
     */
    private $strPath;

    /**
     * @var VersionControl_SVN The svn object.
     */
    private $svn = null;

    /**
     * @var Xinc_Plugin_Repos_ModificationSet_Svn_Task The task config.
     */
    private $task = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $this->strPath = Xinc_Ini::getInstance()->get('path', 'svn');
        } catch (Exception $e) {
            $this->strPath = 'svn';
        }
    }

    /**
     * Returns definition of task.
     *
     * @return array Array of definition.
     */
    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_ModificationSet_Svn_Task($this));
    }


    /**
     * Checks whether the Subversion project has been modified.
     *
     * @param Xinc_Build_Interface                       $build The running build.
     * @param Xinc_Plugin_Repos_ModificationSet_Svn_Task $task  The configured task
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result The result of the check.
     */
    public function checkModified(
        Xinc_Build_Interface $build,
        Xinc_Plugin_Repos_ModificationSet_Svn_Task $task
    ) {
        $result = new Xinc_Plugin_Repos_ModificationSet_Result();

        try {
            $this->task = $task;
            $this->svn = VersionControl_SVN::factory(
                array('info', 'log'), 
                array(
                    'fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_ASSOC,
                    // @TODO VersionControl_SVN doesn't work as the documentation tolds.
                    'path'      => $task->getDirectory(),
                    'url'       => $task->getRepository(),
                    'username'  => $task->getUsername(),
                    'password'  => $task->getPassword(),
                )
            );

            $strRemoteVersion = $this->getRemoteVersion();
            $strLocalVersion = $this->getLocalVersion();
        } catch(Exception $e) {
            $build->error('Test of Subversion failed: ' . $e->getMessage());
            $build->setStatus(Xinc_Build_Interface::FAILED);
            $result->setStatus(
                Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR
            );
            return $result;
        }
        

        $result->setRemoteRevision($strRemoteVersion);
        $result->setLocalRevision($strLocalVersion);


        if ($strRemoteVersion !== $strLocalVersion) {
            $this->fetch();
            $this->getModifiedFiles($result);
            $this->getChangeLog($result);

            if ($task->getUpdate()) {
                try {
                    $this->update();
                } catch(Exception $e) {
                    $build->error('Update of GIT local failed: ' . $e->getMessage());
                    $result>setStatus(
                        Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED
                    );
                    return $result;
                }
            }
            $result->setStatus(
                Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED
            );
        }

        return $result;
    }

    protected function getRemoteVersion()
    {
        $this->svn->info->run(
            array($this->task->getRepository()),
            array('xml' => true)
        );
        die('A');
    }


    protected function getLocalVersion()
    {
        var_dump($this->svn->info->run(
            array($this->task->getDirectory()),
            array('xml' => true)
        ));
    }

    protected function getChangeLog(
        Xinc_Build_Interface $build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result $set,
        $fromRevision, $toRevision, $username, $password
    ) {
        if ($fromRevision < $toRevision) {
            $fromRevision++;
        }
        $credentials = '';
        if ($username != null) { 
            $credentials .= ' --username ' . $username; 
        }
        if ($password != null) { 
            $credentials .= ' --password ' . $password; 
        }
        exec($this->_svnPath . '  log -r ' . $fromRevision . ':' . $toRevision . ' --xml '
            . $credentials . ' ' . $dir,
            $output, $result);
        if ($result == 0) {
            array_shift($output);
            $xml = new SimpleXMLElement(join('', $output));
            $entries = $xml->xpath("//logentry");
            foreach ($entries as $entry) {
                $attributes = $entry->attributes();
                $revision = (int) $attributes->revision;
                $author = (string) $entry->author;
                $dateStr = (string) $entry->date;
                $dateArr = preg_match("/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}).*?([Z+-])(.*)/",
                                      $dateStr, $matches);
                $zIndicator = $dateArr[7];
                $year = $dateArr[1];
                $month = $dateArr[2];
                $day = $dateArr[3];
                $hours = $dateArr[4];
                $minutes = $dateArr[5];
                $seconds = $dateArr[6];
                $timestamp = mktime($hours, $minutes, $seconds, $month, $day, $year);
                if ($zIndicator != 'Z') {
                    $addArr = split(':', $dateArr[8]);
                    if (count($addArr)>1) {
                        $addHours = $addArr[0];
                        $addMinutes = $addArr[1];
                        if ($zIndicator == '+') {
                            $timestamp += $addHours * 60 * 60;
                        } else if ($zIndicator == '-') {
                            $timestamp -= $addHours * 60 * 60;
                        }
                    }
                }

                $message = (string) $entry->msg;

                $set->addLogMessage($revision, $timestamp, $author, $message);
            }
        } else {
            $strOutput = join('', $output);
            if ($username != null || $password != null) {
                $strOutput = $this->_maskOutput($strOutput, array($username, $password));
            }
            $build->error('Could not retrieve log messages from svn: ' . $strOutput);
        }
    }

    protected function getModifiedFiles(
        Xinc_Build_Interface $build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result $set,
        $username, $password
    ) {
        $credentials = '';
        if ($username != null) { 
            $credentials .= ' --username ' . $username; 
        }
        if ($password != null) {
            $credentials .= ' --password ' . $password; 
        }
        exec($this->_svnPath . ' status -u --xml ' . $credentials . ' ' . $dir, $output, $result);

        if ($result == 0) {
            try {
                array_shift($output);
                $xml = new SimpleXMLElement(join('', $output));
                $basePaths = $xml->xpath("/status/target");
                $basePath = $basePaths[0];
                $baseAttributes = $basePath->attributes();
                $basePathName = (string) $baseAttributes->path;

                $set->setBasePath($basePathName);

                $entries = $xml->xpath("//entry");
                //$build->info(var_export($entries,true));
                //var_dump($entries);
                foreach ($entries as $entry) {
                    $attributes = $entry->attributes();
                    $fileName = (string) $attributes->path;
                    $author = null;
                    $reposStatus = $entry->{'repos-status'};
                    if ($reposStatus) {
                        $reposAttributes = $reposStatus->attributes();
                        $reposStatus = (string)$reposAttributes->item;
                    } else {
                        $reposStatus = '';
                    }
                    switch ($reposStatus) {
                        case 'modified':
                            $set->addUpdatedResource($fileName, $author);
                            break;
                        case 'deleted':
                            $set->addDeletedResource($fileName, $author);
                            break;
                        case 'added':
                            $set->addNewResource($fileName, $author);
                            break;
                        case 'conflict':
                            $set->addConflictResource($fileName, $author);
                            break;
                    }
                }
            } catch (Exception $e) {
                $build->error('Could not parse modification set xml.');
            }
        } else {
            $strOutput = join('', $output);
            if ($username != null || $password != null) {
                $strOutput = $this->_maskOutput($strOutput, array($username, $password));
            }
            $build->error('SVN status query failed: ' . $strOutput);
        }
    }

    private function update(
        Xinc_Build_Interface $build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result $set,
        $username, $password
    ) {
        $credentials = '';
        if ($username != null) { 
            $credentials .= ' --username ' . $username; 
        }
        if ($password != null) { 
            $credentials .= ' --password ' . $password; 
        }
        exec($this->_svnPath . ' update ' . $credentials . ' ' . $dir, $output, $result);
        if ($result == 0) {
            $build->getProperties()->set('svn.revision', $set->getRemoteRevision());
            $build->info('Update of SVN working copy succeeded.');
        } else {
            $strOutput = join('', $output);
            if ($username != null || $password != null) {
                $strOutput = $this->_maskOutput($strOutput, array($username, $password));
            }
            $build->error('Update of SVN working copy failed: ' . $strOutput);
            $set->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
        }
    }

    /**
     * Masks certain string elements with **** and returns the string
     *
     * @param string $inputStr
     * @param array $maskElements
     *
     * @return string
     */
    private function _maskOutput($inputStr, array $maskElements)
    {
        $outputStr = str_replace($maskElements, '****', $inputStr);
        return $outputStr;
    }

    /**
     * Validate if the plugin can run properly on this system
     *
     * @return boolean True if plugin can run properly otherwise false.
     */
    public function validate()
    {
        if (!@include_once 'VersionControl/SVN.php') {
            Xinc_Logger::getInstance()->error(
                'PEAR:VersionControl_SVN not installed.'
            );
            return false;
        }
        return true;
    }
}