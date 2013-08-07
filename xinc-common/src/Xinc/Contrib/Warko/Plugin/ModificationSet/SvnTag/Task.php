<?php
/**
 * Xinc - Continuous Integration.
 * task to define which branches/tags should trigger a build
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

require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';

class Xinc_Contrib_Warko_Plugin_ModificationSet_SvnTag_Task extends Xinc_Plugin_Repos_ModificationSet_AbstractTask
{
    private $_directory = '.';
    protected $_prefix = '';
    protected $_property = null;
    private $_switch = false;
    protected $_tagNameProperty = null;

    /**
     * Check if this modification set has been modified
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result
     */
    public function checkModified(Xinc_Build_Interface $build)
    {
        $res = $this->plugin->checkModified(
            $build,
            $this->_directory,
            $this->_prefix,
            $this->_switch,
            $this->_tagNameProperty
        );
        if ($res->isChanged() && !empty($this->_property)) {
            // a modification in this tag has been detected
            $build->getProperties()->set($this->_property, true);
            $build->info('Property "' . $this->_property . '" set to TRUE');
        }
        return $res;
    }

    public function getName()
    {
         return 'svntag';
    }

    public function setTagNameProperty($value)
    {
        $this->_tagNameProperty = $value;
    }

    public function setDirectory($directory)
    {
        $this->_directory = $directory;
    }

    public function setSwitch($switch)
    {
        $this->_switch = $switch;
    }

    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    public function setProperty($property)
    {
        $this->_property = $property;
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validateTask()
    {
        if (!isset($this->_directory)) {
            throw new Xinc_Exception_MalformedConfig(
                'Element modificationSet/svntag - required attribute "directory" is not set'
            );
        }
        if (!isset($this->_prefix)) {
            throw new Xinc_Exception_MalformedConfig(
                'Element modificationSet/svntag - required attribute "prefix" is not set'
            );
        }
        $file = $this->_directory;
        $file2 = Xinc::getInstance()->getWorkingDir() . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($file) && !file_exists($file2)) {
            Xinc_Logger::getInstance()->error('Directory ' . $file2 . ' does not exist');
            return false;
        } elseif (file_exists($file2)) {
            $this->_directory = $file2;
        }
        return true;
    }
}
