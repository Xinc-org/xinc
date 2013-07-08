<?php
/**
 * Xinc - Continuous Integration.
 * Repository to manage all registered Plugins
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin
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

require_once 'Xinc/Plugin/Task/Exception.php';
require_once 'Xinc/Plugin/Iterator.php';
require_once 'Xinc/Gui/Widget/Repository.php';
require_once 'Xinc/Api/Module/Repository.php';

class Xinc_Plugin_Repository
{
    private static $_instance;
    
    /**
     * @var Xinc_Plugin_Task_Interface[]
     */
    private $_definedTasks = array();
    
    /**
     * @var Xinc_Plugin_Interface[]
     */
    private $_plugins = array();
    
    /**
     * Holding a reference from the task to
     * the slot they are working in
     *
     * @var array
     */
    private $_slotReference = array();
    
    /**
     * Get an instance of the Plugin Repository
     *
     * @return Xinc_Plugin_Repository
     */
    public static function getInstance()
    {
        if (!Xinc_Plugin_Repository::$_instance) {
            Xinc_Plugin_Repository::$_instance = new Xinc_Plugin_Repository();
        }
        return Xinc_Plugin_Repository::$_instance;
    }

    /**
     * Enter description here...
     *
     * @param Xinc_Plugin_Interface $plugin
     *
     * @return boolean
     * @throws Xinc_Plugin_Task_Exception
     */
    public function registerPlugin(Xinc_Plugin_Interface $plugin)
    {
        $pluginClass = get_class($plugin);
        if (!$plugin->validate()) {
            Xinc_Logger::getInstance()->error(
                'cannot load plugin ' . $pluginClass
            );
                                 
            return false;
        }
        $tasks = $plugin->getTaskDefinitions();

        $task = null;
        foreach ($tasks as $task) {
            $taskClass = get_class($task);
            $fullTaskName = $task->getName();
            $taskSlot = $task->getPluginSlot();

            switch ($taskSlot) {
                case Xinc_Plugin_Slot::PROJECT_SET_VALUES: 
                        // make sure the task implements the setter interface
                        if (!$task instanceof Xinc_Plugin_Task_Setter_Interface) {
                            Xinc_Logger::getInstance()->error(
                                'cannot register task ' . $fullTaskName
                                . ' it does not implement the required interface '
                                . 'Xinc_Plugin_Task_Setter_Interface'
                            );
                            continue;
                        }
                    break;
                default:
                    break;
            }

            /**
             * Register task for the slot
             */
            if (!isset($this->_slotReference[$taskSlot])) {
                $this->_slotReference[$taskSlot] = array();
            }
            $this->_slotReference[$taskSlot][] = &$task;

            $parentTasks  = array(); //$task->getAllowedParentElements(); // should return the tasks! not the string
            if (count($parentTasks)>0) {
                $this->_registerTaskDependencies($plugin, $task, $parentTasks);
            } else {

                $fullTaskName = strtolower($fullTaskName);
                
                if (isset($this->_definedTasks[$fullTaskName])) {
                        throw new Xinc_Plugin_Task_Exception();
                }
                $this->_definedTasks[$fullTaskName] = array(
                    'classname'=> $taskClass,
                    'plugin'   => array('classname'=> $pluginClass)
                );

                    // register default classname as task
                $classNameTask = strtolower($taskClass);
                if (isset($this->_definedTasks[$classNameTask])) {
                    throw new Xinc_Plugin_Task_Exception();
                }
                $this->_definedTasks[$classNameTask] = array(
                    'classname'=> $taskClass,
                    'plugin'   => array('classname' => $pluginClass)
                );
            }
        }

        $widgets = $plugin->getGuiWidgets();
        foreach ($widgets as $widget) {
            Xinc_Gui_Widget_Repository::getInstance()->registerWidget($widget);
        }
        
        $apiModules = $plugin->getApiModules();
        foreach ($apiModules as $apiMod) {
            Xinc_Api_Module_Repository::getInstance()->registerModule($apiMod);
        }
        
        $this->_plugins[] = $plugin;
    }

    /**
     *
     * @param Xinc_Plugin_Interface $plugin
     * @param Xinc_Plugin_Task_Interface $task
     * @param array $parentTasks
     *
     * @throws Xinc_Plugin_Task_Exception
     */
    private function _registerTaskDependencies(Xinc_Plugin_Interface $plugin,
                                               Xinc_Plugin_Task_Interface $task,
                                               array $parentTasks
    ) {    
        $taskClass = get_class($task);
        $pluginClass = get_class($plugin);
        $fullTaskNames = array();
        foreach ($parentTasks as $parentTask) {
            if ($parentTask instanceof Xinc_Plugin_Task_Interface ) {
                $parentTaskClass = get_class($parentTask);
                $fullTaskNames[] = $parentTask->getName() . '/' . $task->getName();
                $fullTaskNames[] = $parentTaskClass . '/' . $taskClass;
            }
        }
        foreach ($fullTaskNames as $fullTaskName) {
            $fullTaskName = strtolower($fullTaskName);

            if (isset($this->_definedTasks[$fullTaskName])) {
                throw new Xinc_Plugin_Task_Exception();
            }
            $this->_definedTasks[$fullTaskName] = array(
                'classname'=> $taskClass,
                'plugin'   => array('classname'=> $pluginClass)
            );
        }
    }

    public function &getTask($taskname, $parentElement = null)
    {
        $taskname = strtolower($taskname);
        if ($parentElement!=null) {
            $taskname2  = $parentElement . '/' . $taskname;
        }

        if (isset($this->_definedTasks[$taskname2])) {
            $taskData = $this->_definedTasks[$taskname2];
        } else if (isset($this->_definedTasks[$taskname])) {
            $taskData = $this->_definedTasks[$taskname];
        } else {
            
            throw new Xinc_Plugin_Task_Exception('undefined task '.$taskname);
        }

        if ( !isset($this->_plugins[$taskData['plugin']['classname']]) ) {
            
            $plugin = new $taskData['plugin']['classname'];
            $this->_plugins[$taskData['plugin']['classname']] = &$plugin;

        } else {
            $plugin = $this->_plugins[$taskData['plugin']['classname']];
        }

        $className = $taskData['classname'];
        $object = new $className($plugin);
        return $object;
    }
    
    /**
     * Returns Plugin Iterator
     *
     * @return Xinc_Iterator
     */
    public function getPlugins()
    {
        return new Xinc_Plugin_Iterator($this->_plugins);
    }

    /**
     * Returns all tasks that are registered
     * for a specific slot
     *
     * @param int $slot @see Xinc_Plugin_Slot
     *
     * @return Xinc_Iterator
     */
    public function getTasksForSlot($slot)
    {
        if (!isset($this->_slotReference[$slot])) {
            return new Xinc_Iterator();
        } else {
            return new Xinc_Iterator($this->_slotReference[$slot]);
        }
    }

    public static function tearDown()
    {
        self::$_instance = null;
    }

}