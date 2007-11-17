<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc
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
    public function checkModified(Xinc_Project &$project, $dir)
    {
        if (!file_exists($dir)) {
            //throw new Xinc_Exception_ModificationSet('Subversion checkout '
            //                                        . 'directory not present');
            $project->error('Subversion checkout directory'
                                             . ' not present');
            return -1;
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
                throw new Xinc_Exception_ModificationSet('Problem with remote '
                                                        . 'Subversion repository');
            }

            $localRevision = $this->getRevision($localSet);
            $remoteRevision = $this->getRevision($remoteSet);
                
            $project->debug('Subversion checkout dir is '.$dir.' '
                           .'local revision @ '.$localRevision.' '
                           .'Remote Revision @ '.$remoteRevision);
            chdir($cwd);
            return $localRevision < $remoteRevision;
        } else {
            //var_dump($output);
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
        $list = split("\n", $result);
        foreach ($list as $row) {
            $field = split(':', $row);
            if (preg_match('/Revision/', $field[0])) {
                return trim($field[1]);
            }
        }
    }
    /**
     * Check necessary variables are set
     *
     * @throws Xinc_Exception_MalformedConfig
     */
    public function validate()
    {
        exec('svn help', $output, $result);
        /**
         * See Issue 56
         */
        $foundSvn = false;
        for ($i=0; $i < count($output); $i++) {
            $parts=preg_split('/\s+/', $output[$i]);
            /**
             * make sure we get some help output from svn help, to assure that its
             * installed
             */
            if ( $parts[0]=='usage:' && $parts[1]=='svn') {
                $foundSvn = true;
                break;
            }
        }
        if (!$foundSvn) {
            Xinc_Logger::getInstance()->error('command "svn" not found');
                
            return false;
        } else {
            return true;
        }

    }
}