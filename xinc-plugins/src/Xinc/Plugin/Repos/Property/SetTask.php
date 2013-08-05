<?php
/**
 * Xinc - Continuous Integration.
 * Property setter task
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Property
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

require_once 'Xinc/Plugin/Task/Abstract.php';

class Xinc_Plugin_Repos_Property_SetTask extends Xinc_Plugin_Task_Abstract
{
    private $_lastPair = array();

    /**
     *
     * @var string
     */
    private $_name;

    private $_if;

    private $_file;

    /**
     *  Holding all the property value pairs
     *
     * @var array
     */
    private $_properties = array();

    /**
     *
     * @var string
     */
    private $_value;

    /**
     * sets the name of the property
     *
     * @param string $name
     */
    public function setName($name, $build)
    {
        $this->_name = (string)$name;
        if (isset($this->_name) && isset($this->_value)) {
            $build->getProperties()->set($this->_name, $this->_value);
        }
    }

    /**
     * sets the value of the property
     *
     * @param string $value
     */
    public function setValue($value, $build)
    {
        $this->_value = (string)$value;
        if (isset($this->_name) && isset($this->_value)) {
            $build->getProperties()->set($this->_name, $this->_value);
        }
    }

    public function setIf($if)
    {
        $this->_if = $if;
    }

    public function setFile($fileName)
    {
        $this->_file = $fileName;
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate()
    {
        if (count($this->arSubtasks) > 0) {
            /**
             * cannot have subtasks
             */
            return false;
        }
        if (!isset($this->_name) && !isset($this->_value) && !isset($this->_file)) {
            return false;
        } else if (isset($this->_file) && (isset($this->_name) || isset($this->_value))) {
            return false;
        } else if ((isset($this->_name) && !isset($this->_value)) || (!isset($this->_name) && isset($this->_value))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns name of Task.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return 'property';
    }

    /**
     * Returns the slot of this task inside a build.
     *
     * @return integer The slot number.
     */
    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::INIT_PROCESS;
    }

    public function process(Xinc_Build_Interface $build)
    {
        if ($this->_if !== null) {
            /**
             * If we have a condition, we need to check
             */
            $property = $build->getProperties()->get($this->_if);
            if ($property !== true) {
                $build->info('Property: ' . $this->_name . ' does not apply, ' . $this->_if . ' == false');
                $build->setStatus(Xinc_Build_Interface::PASSED);
                return;
            }
        }
        if (isset($this->_file)) {
            if (file_exists($this->_file)) {
                $build->info('Reading property file: ' . $this->_file);
                $this->_plugin->parsePropertyFile($build, $this->_file);
            } else if (strstr($this->_file, '${')) {
                // not substituted yet, will not use it
            } else {
                $build->error('Cannot read property file: ' . $this->_file);
            }
        } else {
            $build->debug('Setting property "${' . $this->_name . '}" to "' . $this->_value . '"');
            $build->getProperties()->set($this->_name, $this->_value);
            $build->setStatus(Xinc_Build_Interface::PASSED);
        }
    }
}
