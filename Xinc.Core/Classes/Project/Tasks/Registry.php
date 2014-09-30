<?php
/**
 * Xinc - Continuous Integration.
 * Interface for holding all tasks that need to be executed to run a project
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

namespace Xinc\Core\Project\Tasks;

class Registry
{
    /**
     * tasks that need to be executed for each slot
     *
     * @var Iterator[]
     */
    private $slots = array();

    /**
     * Adds a task to the slot
     * 
     * this task will be executed in the specified slot
     *
     * @param Xinc_Plugin_Task_Interface $task
     */
    public function registerTask(Xinc_Plugin_Task_Interface $task)
    {
        $slot = $task->getPluginSlot();
        if (!isset($this->slots[$slot])) {
            $this->slots[$slot] = new Xinc_Build_Tasks_Iterator();
        }
        $this->slots[$slot]->add($task);
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

        return $this->slots[$slot];
    }

    /**
     * Get all tasks registered
     *
     * @return Xinc_Build_Tasks_Iterator
     */
    public function getTasks()
    {
        $buildTaskIterator = new Xinc_Build_Tasks_Iterator();
        foreach ($this->slots as $slot => $iterator) {
            while ($iterator->hasNext()) {
                $buildTaskIterator->add($iterator->next());
            }
            $iterator->rewind();
        }

        return $buildTaskIterator;
    }
}
