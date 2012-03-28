<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet.Svn
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

require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';

class Xinc_Plugin_Repos_ModificationSet_Svn_Task
    extends Xinc_Plugin_Repos_ModificationSet_AbstractTask
{
    /**
     * Directory containing the Subversion project.
     *
     * @var string
     */
    private $_directory = '.';

    private $_update = false;

    private $_username = null;

    private $_password = null;

    private $_property;


    /**
     * Returns name of Task.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return 'svn';
    }

    /**
     * Sets the svn checkout directory.
     *
     * @param string
     */
    public function setDirectory($directory)
    {
        $this->_directory = (string)$directory;
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

    public function checkModified(Xinc_Build_Interface $build)
    {
        $res = $this->_plugin->checkModified($build, $this->_directory,
                                             $this->_update, $this->_username,
                                             $this->_password);
        if ($res->isChanged() && !empty($this->_property)) {
            // a modification in this tag has been detected
            $build->getProperties()->set($this->_property, true);
            $build->info("Property '".$this->_property."' set to TRUE");
        }
        return $res;
    }

    public function validateTask()
    {
        if (!isset($this->_directory)) {
            throw new Xinc_Exception_MalformedConfig('Element modificationSet/svn'
                                                    . ' - required attribute '
                                                    . '\'directory\' is not set');
            // @codeCoverageIgnoreStart
        }
            // @codeCoverageIgnoreEnd
        $file = $this->_directory;
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
        return true;
    }
}