<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc.Plugin
 * @author David Ellis
 * @author Gavin Foster
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
require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Svn/Task.php';

require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/ModificationSet.php';

class Xinc_Plugin_Repos_ModificationSet_Svn extends Xinc_Plugin_Base
{
    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_ModificationSet_Svn_Task($this));
    }
    

   


    /**
     * Checks whether the Subversion project has been modified.
     *
     * @return boolean
     */
    public function checkModified(Xinc_Build_Interface &$build, $dir)
    {
        if (!file_exists($dir)) {
            //throw new Xinc_Exception_ModificationSet('Subversion checkout '
            //                                        . 'directory not present');
            $build->error('Subversion checkout directory'
                                             . ' not present');
            return Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED;
        }

        $cwd = getcwd();
        chdir($dir);

        $output = '';
        $result = 9;
        exec('svn info', $output, $result);

        if ($result == 0) {
            $localSet = implode("\n", $output);
            
            $url = $this->getURL($localSet);

            $output = '';
            $result = 9;
            exec('svn info ' . $url, $output, $result);
            $remoteSet = implode("\n", $output);

            if ($result != 0) {
                chdir($cwd);
                /**throw new Xinc_Exception_ModificationSet('Problem with remote '
                                                          . 'Subversion repository');*/
                /**
                 * Dont throw exception, but log error and make build fail
                 */
                $build->error('Problem with remote '
                             . 'Subversion repository, output: ' . $remoteSet);
                $build->setStatus(Xinc_Build_Interface::FAILED);
                /**
                 * return -2 instead of true, see Issue 79
                 */
                return Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED;
            }

            $localRevision = $this->getRevision($localSet);
            $remoteRevision = $this->getRevision($remoteSet);
                
            $build->info('Subversion checkout dir is '.$dir.' '
                           .'local revision @ '.$localRevision.' '
                           .'Remote Revision @ '.$remoteRevision);
            chdir($cwd);
            return $localRevision < $remoteRevision;
        } else {
            chdir($cwd);
            $build->error('Subversion checkout directory '
                         . 'is not a working copy.');
            $build->setStatus(Xinc_Build_Interface::FAILED);
            //throw new Xinc_Exception_ModificationSet('Subversion checkout directory '
            //                                        . 'is not a working copy.');
            return Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED;
        }
    }

    /**
     * Parse the result of an svn command for the Subversion project URL.
     *
     * @param string $result
     * @return string
     */
    private function getUrl($result)
    {
        $list = split("\n", $result);
        foreach ($list as $row) {
            $field = split(': ', $row);
            if (preg_match('/URL/', $field[0])) {
                return trim($field[1]);
            }
        }
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
        exec('svn --version', $versionOutput);
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
                        return $this->_getRevisionNew($result);
                        break;
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
        exec('svn 2>&1', $output, $result);
        /**
         * See Issue 56, check r
         */
        
        if ($result != 1) {
            Xinc_Logger::getInstance()->error('command "svn" not found');
                
            return false;
        } else {
            return true;
        }

    }
}