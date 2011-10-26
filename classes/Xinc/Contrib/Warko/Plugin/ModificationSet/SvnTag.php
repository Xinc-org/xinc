<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Plugin to generate builds from branches and tags
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Contrib
 * @author    Olivier Hoareau <username@example.org>
 * @author    Arno Schneider <username@example.org>
 * @copyright 2007 David Ellis, One Degree Square
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
require_once 'Xinc/Contrib/Warko/Plugin/ModificationSet/SvnTag/Task.php';
require_once 'Xinc/Ini.php';

class Xinc_Contrib_Warko_Plugin_ModificationSet_SvnTag
    extends Xinc_Plugin_Base
{
    private $_svnPath;
    
    public function __construct()
    {
        $svnPath = Xinc_Ini::get('path', 'svn');
        if (empty($svnPath)) {
            $svnPath = 'svn';
        }
        $this->_svnPath = $svnPath;
    }
    
    protected function _getSvnSubDir()
    {
        return 'tags';
    }
    
    public function getTaskDefinitions()
    {
        return array(new Xinc_Contrib_Warko_Plugin_ModificationSet_SvnTag_Task($this));
    }

    public function checkModified(Xinc_Build_Interface $build,
                                  $dir, $prefix, $switch = false,
                                  $svnFolderProperty = null)
    {
        $modResult = new Xinc_Plugin_Repos_ModificationSet_Result();
        if (!file_exists($dir)) {
            $build->error('Subversion checkout directory not present');
            $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
            return $modResult;
        }

        $cwd = getcwd();
        chdir($dir);

        $output = '';
        $result = 9;
        exec($this->_svnPath . ' info', $output, $result);
        
        $found =false;
        if ($result == 0) {
            $localSet = implode("\n", $output);
            $localRev = $this->getRevision($localSet);
            $remoteRev = 0;
            $url = $this->getRootURL();
            $output = '';
            $result = 9;
            exec($this->_svnPath . ' ls --xml ' . $url.'/' . $this->_getSvnSubDir(), $output, $result);
            $remoteSet = implode("\n", $output);
            if ($result != 0) {
                $build->setStatus(Xinc_Build_Interface::FAILED);
                $build->error('Problem with remote Subversion repository');
                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR);
                return $modResult;
            }
            $xml = new SimplexmlElement($remoteSet);
            foreach ($xml->list as $i=>$list) {
                foreach ($list->entry as $entry) {
                    if (substr($entry->name, 0, strlen($prefix)) != $prefix
                        && 
                        !preg_match('/' . $prefix . '/', $entry->name)
                    ) continue;
                    $attributes = $entry->attributes();
                    if(strtolower((string)$attributes['kind'])!='dir') continue;
                    $attributes = $entry->commit->attributes();    
                    $rev = (int)$attributes->revision;
                    if ($rev>$localRev) {
                        $tagName = (string)$entry->name;
                        if ($svnFolderProperty != null) {
                            $build->getProperties()->set($svnFolderProperty, $tagName);
                        }
                        // switch to the latest release
                        if ($switch) {
                            
                            exec($this->_svnPath . ' switch ' . $url . '/' . $this->_getSvnSubDir()
                                . '/' . $tagName, $switchOut, $switchRes);
                            if ($switchRes != 0) {
                                $build->error('Could not switch to tag :' . $tagName . ', result:' 
                                              . implode("\n", $switchOut));
                                $build->setStatus(Xinc_Build_Interface::FAILED);
                                $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED);
                                return $modResult;
                            }
                        }
                        $remoteRev = $rev;
                        $found = true;
                    }
                }
            }
            if ($remoteRev<=0) {
                    $build->info('Subversion checkout dir is '.$dir.' '
                                   .'local revision @ '.$localRev.' '
                                   .'No remote revision with matching tag prefix ('.$prefix.')');
            } else {
                    $build->info('Subversion checkout dir is '.$dir.' '
                                   .'local revision @ '.$localRev.' '
                                   .'Last remote revision with matching tag prefix @ '.$remoteRev.' ('.$prefix.')');
            }
        chdir($cwd);
        $modResult->setLocalRevision($localRev);
        $modResult->setRemoteRevision($remoteRev);
        if ($modResult->isChanged()) {
            $modResult->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED);
        }
        return $modResult;
        } else {
            chdir($cwd);
            throw new Xinc_Exception_ModificationSet('Subversion checkout directory '
                                                    . 'is not a working copy.');
        }
    }

    /**
     * Parse the result of an svn command for the Subversion project URL.
     *
     * @param string $result
     * @return string
     */
    private function getRootUrl()
    {
        exec($this->_svnPath . ' info --xml', $output, $result);
        $xml = new SimpleXMLElement(implode("\n", $output));
        $elements = $xml->xpath("/info/entry/repository/root");
        $rootUrl = (string) $elements[0];
        Xinc_Logger::getInstance()->debug('Getting root url: ' . $rootUrl);
        return  $rootUrl;
    }

    /**
     * Parse the result of an svn command
     * for the Subversion project revision number.
     *
     * @param string $result
     * @return string
     */
    public function getRevision($result)
    {
        /**
         * get the version
         */
        exec($this->_svnPath . ' --version', $versionOutput);
        $versionLine = $versionOutput[0];
        preg_match('/.*? (\d.\d.\d) .*?/', $versionLine, $matches);
        $version = $matches[1];
        list($major, $minor, $point) = split('\.', $version);

        Xinc_Logger::getInstance()->debug('Using svn version: ' . "$major.$minor.$point");
        switch ($major) {
            case 1:
                switch ($minor) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                        /**
                         * Get after URL
                         */
                        return $this->_getRevisionOldOld($result);
                        break;
                    default:
                        /**
                         * take after UUID
                         */
                        return $this->_getRevisionXml($result);
                }
                break;
            default:
                /**
                 * Use the /Revision/ pattern
                 * */
                return $this->_getRevisionOld($result);
                break;
        }


    }
    
    private function _getRevisionXml($x)
    {
        exec($this->_svnPath . ' info --xml', $output, $result);
        $remoteSet = implode("\n", $output);
        if ($result != 0) {
            return -1;
        }
        $xml = new SimpleXMLElement($remoteSet);
        $commits = $xml->xpath("/info/entry/commit");
        $commit = $commits[0];
        $attributes = $commit->attributes();
        $rev = (int)$attributes->revision;
        return $rev;
    }

    /**
     * Takes the svn info output of a working copy
     * and looks for R[eé]vision to identify the current revision of
     * the working dir. This is only used for very old svn versions, since
     * we cannot identify them correclty for all locales
     *
     * @param $result the svn info output
     * @return integer the revision number
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
     * Relying on the output of version 1.4 and up to have the
     * actual revision one line after the UUID line. Return
     *
     * @param $result the svn info output
     * @return integer the revision number
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
     * @return integer the revision number
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
        exec($this->_svnPath . ' help' . $redirectErrors, $output, $result);
        /**
         * See Issue 56, check r
         */

        if ($result != 0) {
            Xinc_Logger::getInstance()->error('command "svn" not found');

            return false;
        } else {
            return true;
        }

    }

}
