<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet.Git
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2011 Alexander Opitz, Leipzig
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

require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';

class Xinc_Plugin_Repos_ModificationSet_Git_Task
    extends Xinc_Plugin_Repos_ModificationSet_AbstractTask
{
    /**
     * Directory containing the Git project.
     *
     * @var string
     */
    private $strPath = '.';

    private $_update = false;

    private $_username = null;

    private $_password = null;

    private $_property;

    /**
     * @var VersionControl_Git The git object.
     */
    private $git = null;

    public function getName()
    {
        return 'git';
    }

    public function registerTask(Xinc_Plugin_Task_Interface $task)
    {
        $this->_subtasks[]=$task;
    }

    /**
     * Sets the svn checkout directory.
     *
     * @param string
     */
    public function setDirectory($directory)
    {
        $this->strPath = (string)$directory;
    }

    /**
     * sets the name of the property, which will be set to
     * TRUE in case a modification was detected
     *
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->_property = (string) $property;
    }

    /**
     * Sets the username for the svn commands
     *
     * @param string
     */
    public function setUsername($username)
    {
        $this->_username = (string)$username;
    }

    /**
     * Sets the password for the svn commands
     *
     * @param string
     */
    public function setPassword($password)
    {
        $this->_password = (string)$password;
    }

    /**
     * Tells whether to update the working copy directly here or not
     *
     * @param string $update
     */
    public function setUpdate($update)
    {
        $update = (string) $update;
        $this->_update = in_array($update, array('true', '1')) ? true:false;
    }

    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::PRE_PROCESS;
    }

    public function checkModified(Xinc_Build_Interface $build)
    {
        var_dump('A');
        var_dump($this->git->getCommits());
        $res = $this->_plugin->checkModified($build, $this->strPath,
                                             $this->_update, $this->_username,
                                             $this->_password);
/*        if ($res->isChanged() && !empty($this->_property)) {
            // a modification in this tag has been detected
            $build->getProperties()->set($this->_property, true);
            $build->info("Property '".$this->_property."' set to TRUE");
        }*/
        return $res;
    }

    public function validateTask()
    {
        if (!isset($this->strPath)) {
            throw new Xinc_Exception_MalformedConfig('Element modificationSet/git'
                                                    . ' - required attribute '
                                                    . '\'directory\' is not set');
        }

        $this->git = new VersionControl_Git($this->strPath);

        try {
            var_dump($this->git->getHeadCommits());
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
/*        $file = $this->_directory;
        $file2 = Xinc::getInstance()->getWorkingDir() . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($file) && !file_exists($file2)) {
            Xinc_Logger::getInstance()->error('Directory '.$file2.' does not exist');
            return false;
        } else if (file_exists($file2)) {
            $this->_directory = $file2;
            //Xinc_Logger::getInstance()->error("Directory $file2 does not exist");
            //return false;
        }
        //return false;
        return true;*/
    }
}