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
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Svn/Task.php';
require_once 'Xinc/Ini.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/ModificationSet.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Result.php';

class Xinc_Plugin_Repos_ModificationSet_Svn extends Xinc_Plugin_Base
{
    private $_svnPath;

    public function __construct()
    {
        try {
            $svnPath = Xinc_Ini::getInstance()->get('path', 'svn');
        } catch (Exception $e) {
            $svnPath = null;
        }
        if (empty($svnPath)) {
            $svnPath = 'svn';
        }
        $this->_svnPath = $svnPath;
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_ModificationSet_Svn_Task($this));
    }

    protected function _getChangeLog(
        Xinc_Build_Interface &$build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result &$set,
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
        Xinc_Build_Interface &$build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result &$set,
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
        Xinc_Build_Interface &$build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result &$set,
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
     * Checks whether the Subversion project has been modified.
     *
     * @return boolean
     */
    public function checkModified(Xinc_Build_Interface &$build,
                                 $dir, $update = false,
                                 $username = null, $password = null)
    {
        $modResult = new Xinc_Plugin_Repos_ModificationSet_Result();
        if (!file_exists($dir)) {
            //throw new Xinc_Exception_ModificationSet('Subversion checkout '
            //                                        . 'directory not present');
            $build->error('Subversion checkout directory'
                                             . ' not present');
            $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED);
            return $modResult;
        }

        $cwd = getcwd();
        chdir($dir);

        $output = '';
        $result = 9;
        $credentials = '';
        if ($username != null) { 
            $credentials .= ' --username ' . $username; 
        }
        if ($password != null) { 
            $credentials .= ' --password ' . $password; 
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
        return $this->_getRevisionXml($result);
    }

    /**
     * Takes the svn info output of a working copy
     * and looks for R[eé]vision to identify the current revision of
     * the working dir. This is only used for very old svn versions, since
     * we cannot identify them correclty for all locales
     * 
     * @param $result the svn info output
     *
     * @return integer the revision number
     * @deprecated 
     */
    protected function _getRevisionOld($result)
    {
        $list = split("\n", $result);
        foreach ($list as $row) {
            $field = split(':', $row);
            if (preg_match('/R[eé]vision/', $field[0])) {
                return trim($field[1]);
            }
        }
        return null;
    }

    /**
     * Enter description here...
     *
     * @param string $xmlString
     *
     * @return integer
     * @throws Exception
     */
    private function _getRevisionXml($xmlString)
    {
        $xml = new SimpleXMLElement($xmlString);
        $commits = $xml->xpath("/info/entry/commit");
        $commit = $commits[0];
        $attributes = $commit->attributes();
        $rev = (int)$attributes->revision;
        return $rev;
    }

    /**
     * Relying on the output of version 1.4 and up to have the
     * actual revision one line after the UUID line. Return
     * 
     * @param $result the svn info output
     *
     * @return integer the revision number
     * @deprecated 
     */
    protected function _getRevisionNew($result)
    {
        $list = split("\n", $result);
        for ($i=0; $i<count($list); $i++) {
            $field = split(':', $list[$i]);
            if (preg_match('/.*UUID.*/', $field[0])) {
                $revisionRow = $list[$i+1];
                $revisionField = split(':', $revisionRow);
                return trim($revisionField[1]);
            }
        }
        return null;
    }

    /**
     * Relying on the output of version >1.3 and up to have the
     * actual revision one line after the URL line.
     *
     * @param $result the svn info output
     *
     * @return integer the revision number
     * @deprecated
     */
    protected function _getRevisionOldOld($result)
    {
        $list = split("\n", $result);
        for ($i=0; $i<count($list); $i++) {
            $field = split(':', $list[$i]);
            if (preg_match('/.*URL.*/', $field[0])) {
                $revisionRow = $list[$i+1];
                $revisionField = split(':', $revisionRow);
                return trim($revisionField[1]);
            }
        }
        return null;
    }

    /**
     * Check necessary variables are set
     *
     * @throws Xinc_Exception_MalformedConfig
     */
    public function validate()
    {
        if (DIRECTORY_SEPARATOR == '/') {
            // we are on Linux/Unix
            $redirectErrors = ' 2>&1';
        } else {
            $redirectErrors = ' ';
        }
        exec($this->_svnPath . ' help' . $redirectErrors, $dummy, $result);
        /**
         * See Issue 56, check r
         */

        if ($result != 0) {
            Xinc_Logger::getInstance()->error('command "svn" not found');

            return false;
        } else {
            if (DIRECTORY_SEPARATOR == '/') {
                // we are on Linux/Unix
                $redirectErrors = ' 2>&1';
            } else {
                $redirectErrors = ' ';
            }
            /**
             * check if we have the svn info --xml option
             */
            exec($this->_svnPath . ' info --xml' . $redirectErrors, $output, $result);
            /**
             * We need to check 1 and 2 for <info> since it depends 
             * if the place where its called is a working copy or not
             */
            if (trim($output[1])=='<info>' || trim($output[2])=='<info>') {
                return true;
            } else {
                Xinc_Logger::getInstance()->error('SVN version does not support "svn info --xml".' 
                                                  . ' This is used to gather info about svn working copies.'
                                                  . 'Please upgrade your svn version.');
                return false;
            }
        }
    }
}