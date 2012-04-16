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

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Svn/Task.php';
require_once 'Xinc/Ini.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/ModificationSet.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Result.php';

class Xinc_Plugin_Repos_ModificationSet_Svn
    extends Xinc_Plugin_Base
{
    /**
     * @var string Path to project
     */
    private $strPath;

    /**
     * @var VersionControl_SVN The svn object.
     */
    private $svn = null;

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
            $this->svn = VersionControl_SVN::factory(
                array('info'), 
                array(
                    'fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_ARRAY,
                    'path'      => $task->getDirectory(),
                    'url'       => $task->getRepository(),
                    'username'  => $task->getUsername(),
                    'password'  => $task->getPassword(),
                )
            );
        } catch(Exception $e) {
            
        }
        

        exec($this->_svnPath . ' info ' . $credentials . ' --xml', $output, $result);
        //$build->debug('result of "svn info --xml":' . var_export($output,true));
        if ($result == 0) {
            $localSet = implode("\n", $output);

            try {
                $url = $this->getURL($localSet);
            } catch (Exception $e) {
                $strOutput = $localSet;
                if ($username != null || $password != null) {
                    $strOutput = $this->_maskOutput($strOutput, array($username, $password));
                }
                $build->error('Problem with remote '
                             . 'Subversion repository, cannot get URL of working copy ' . $strOutput);
                $build->setStatus(Xinc_Build_Interface::FAILED);
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
                return $modResult;
            }
            $output = '';
            $result = 9;

            if (DIRECTORY_SEPARATOR == '/') {
                // we are on Linux/Unix
                $redirectErrors = ' 2>&1';
            } else {
                $redirectErrors = ' ';
            }

            exec($this->_svnPath . ' info ' . $credentials . ' ' . $url . ' --xml' . $redirectErrors, $output, $result);
            $remoteSet = implode("\n", $output);

            if ($result != 0) {
                chdir($cwd);
                /**throw new Xinc_Exception_ModificationSet('Problem with remote '
                                                          . 'Subversion repository');*/
                /**
                 * Dont throw exception, but log error and make build fail
                 */
                $strOutput = $remoteSet;
                if ($username != null || $password != null) {
                    $strOutput = $this->_maskOutput($strOutput, array($username, $password));
                }
                $build->error('Problem with remote '
                             . 'Subversion repository, output: ' . $strOutput);
                $build->setStatus(Xinc_Build_Interface::FAILED);
                /**
                 * return -2 instead of true, see Issue 79
                 */
                //return Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED;
                // dont make build fail if there are timeouts
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
                return $modResult;
            }
            try {
                $localRevision = $this->getRevision($localSet);
            } catch (Exception $e) {
                $strOutput = $localSet;
                if ($username != null || $password != null) {
                    $strOutput = $this->_maskOutput($strOutput, array($username, $password));
                }
                $build->error('Problem with remote '
                             . 'Subversion repository, cannot get revision of working copy ' . $strOutput);
                $build->setStatus(Xinc_Build_Interface::FAILED);
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
                return $modResult;
            }
            try {
                $remoteRevision = $this->getRevision($remoteSet);
            } catch (Exception $e) {
                $strOutput = $remoteSet;
                if ($username != null || $password != null) {
                    $strOutput = $this->_maskOutput($strOutput, array($username, $password));
                }
                $build->error('Problem with remote '
                             . 'Subversion repository, cannot get revision of remote repos ' . $strOutput);
                $build->setStatus(Xinc_Build_Interface::FAILED);
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
                return $modResult;
            }

            $build->info('Subversion checkout dir is '.$dir.' '
                           .'local revision @ '.$localRevision.' '
                           .'Remote Revision @ '.$remoteRevision);
            chdir($cwd);
            //$changed = $localRevision < $remoteRevision;
            $modResult->setLocalRevision($localRevision);
            $modResult->setRemoteRevision($remoteRevision);

            if ($update && $modResult->isChanged()) {
                if ($build->getLastBuild()->getStatus() === Xinc_Build_Interface::FAILED) {
                    try {
                        $lastSuccessfulBuild = Xinc_Build_Repository::getLastSuccessfulBuild($build->getProject());
                        //$modResult->mergeResultSet($lastSuccessfulBuild->getProperties()->get('changeset'));
                        $changeSet = $lastSuccessfulBuild->getProperties()->get('changeset');
                        if ($changeSet instanceof Xinc_Plugin_Repos_ModificationSet_Result) {
                            $lasSuccessRev = $changeSet->getRemoteRevision();
                            $this->_getChangeLog($build, $dir, $modResult,
                                                 $lasSuccessRev, $localRevision,
                                                 $username, $password);
                        }
                    } catch (Exception $e) {

                    }
                }
                $this->_getModifiedFiles($build, $dir, $modResult, $username, $password);
                $this->_getChangeLog($build, $dir, $modResult, $localRevision, $remoteRevision, $username, $password);
                $this->_update($build, $dir, $modResult, $username, $password);
                //$build->setStatus(Xinc_Build_Interface::PASSED);
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED);
            } else if ($modResult->isChanged()) {
                if ($build->getLastBuild()->getStatus() === Xinc_Build_Interface::FAILED) {
                    try {
                        $lastSuccessfulBuild = Xinc_Build_Repository::getLastSuccessfulBuild($build->getProject());
                        //$modResult->mergeResultSet($lastSuccessfulBuild->getProperties()->get('changeset'));
                        $changeSet = $lastSuccessfulBuild->getProperties()->get('changeset');
                        if ($changeSet instanceof Xinc_Plugin_Repos_ModificationSet_Result) {
                            $lasSuccessRev = $changeSet->getRemoteRevision();
                            $this->_getChangeLog($build, $dir,
                                                 $modResult, $lasSuccessRev,
                                                 $localRevision, $username, $password);
                        }
                    } catch (Exception $e) {

                    }
                }
                $this->_getModifiedFiles($build, $dir, $modResult, $username, $password);
                $this->_getChangeLog($build, $dir, $modResult, $localRevision, $remoteRevision, $username, $password);
                //$build->setStatus(Xinc_Build_Interface::PASSED);
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED);
            }
            return $modResult;
        } else {
            chdir($cwd);
            $build->error('Subversion checkout directory '
                         . 'is not a working copy.');
            $build->setStatus(Xinc_Build_Interface::FAILED);
            //throw new Xinc_Exception_ModificationSet('Subversion checkout directory '
            //                                        . 'is not a working copy.');
            $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED);
            return $modResult;
        }
    }



    protected function _getChangeLog(
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

    protected function _getModifiedFiles(
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

    private function _update(
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
     * Parse the result of an svn command for the Subversion project URL.
     *
     * @param string $result
     *
     * @return string
     * @throws Exception
     */
    private function getUrl($result)
    {
        $xml = new SimpleXMLElement($result);
        $urls = $xml->xpath('/info/entry/url');
        $url = (string) $urls[0];
        return $url;
    }

    /**
     * Parse the result of an svn command 
     * for the Subversion project revision number.
     *
     * @param string $result
     *
     * @return string
     * @throws Exception
     */
    public function getRevision($result)
    {
        $xml = new SimpleXMLElement($xmlString);
        $commits = $xml->xpath("/info/entry/commit");
        $commit = $commits[0];
        $attributes = $commit->attributes();
        $rev = (int)$attributes->revision;
        return $rev;
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
    }
}