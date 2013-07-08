<?php
/**
 * Xinc - Continuous Integration.
 * Interface for holding all tasks that need to be
 * executed for a build project
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build.Task
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

require_once 'Xinc/Build/Tasks/Iterator.php';

class Xinc_Build_Tasks_Registry
{

    /**
     * tasks that need to be executed
     * for each slot
     *
     * @var Xinc_Build_Tasks_Iterator[]
     */
    private $_slots = array();
    
    /**
     * Adds a task to the slot
     * 
     * this task will be executed in the specified slot
     *
     * @param mixed $slot
     * @param Xinc_Plugin_Task_Interface $task
     */
    public function registerTask(Xinc_Plugin_Task_Interface $task)
    {
        $slot = $task->getPluginSlot();
        if (!isset($this->_slots[$slot])) {
            $this->_slots[$slot] = new Xinc_Build_Tasks_Iterator();
        }
        $this->_slots[$slot]->add($task);
    }

    /**
     * Returns all the build tasks for the specified slot
     *
     * @param mixed $slot
     *
     * @return Xinc_Build_Tasks_Iterator
     */
    public function getTasksForSlot($slot)
    {
        if (!isset($this->_slots[$slot])) {
            $buildIterator = new Xinc_Build_Tasks_Iterator();
            return $buildIterator;
        }
        
        return $this->_slots[$slot];
    }
    
    /**
     * Get all tasks registered
     *
     * @return Xinc_Build_Tasks_Iterator
     */
    public function getTasks()
    {
        $buildTaskIterator = new Xinc_Build_Tasks_Iterator();
        foreach ($this->_slots as $slot => $iterator) {
            while ($iterator->hasNext()) {
                $buildTaskIterator->add($iterator->next());
            }
            $iterator->rewind();
        }
        
        return $buildTaskIterator;
    }
}