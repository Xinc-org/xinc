<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
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

namespace Xinc\Core\Task;

abstract class TaskAbstract extends \Xinc\Core\Registry\RegistryAbstract implements TaskInterface
{
    /**
     * @var Xinc_Plugin_Interface
     */
    protected $plugin;

    /**
     * @var SimpleXMLElement
     */
    protected $xml;

    /**
     * @var integer Task Slot see Xinc_Plugin_Slot
     */
    protected $pluginSlot = null;

    /**
     * @var string Name of the task
     */
    protected $name = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Initialize the task
     *
     * @param Xinc\Core\Job\JobInterface $job Build to initialize this task for.
     *
     * @return void
     */
    public function init(\Xinc\Core\Job\JobInterface $job = null)
    {
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate()
    {
        foreach ($this->arSubtasks as $task) {
            if (!$task->validate()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns name of task by lowercasing class name.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the slot of this task inside a build.
     *
     * @return integer The slot number.
     * @see Xinc/Plugin/Slot.php for available slots
     */
    public function getPluginSlot()
    {
        return $this->pluginSlot;
    }

    /**
     * Gets registered subtask for this task.
     *
     * @return Xinc_Build_Tasks_Iterator
     */
    public function getTasks()
    {
        return new Xinc_Build_Tasks_Iterator($this->arSubtasks);
    }

    public function getXml()
    {
        return $this->xml;
    }

    public function setXml(\SimpleXMLElement $element)
    {
        $this->xml = $element;
    }

    /**
     * Converts a string 'true', '1', 'yes' to a boolean true otherwise false.
     *
     * @param string $value The string to convert.
     *
     * @return boolean True if given value is 'true', '1' or 'yes' otherwise false
     */
    public static function string2boolean($value)
    {
        return in_array($value, array('true', '1', 'yes')) ? true : false;
    }
}
