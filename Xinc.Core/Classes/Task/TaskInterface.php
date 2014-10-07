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

interface TaskInterface
{
    /**
     * Constructor
     */
    public function __construct();

    /**
     * Initialize the task
     *
     * @param Xinc\Core\Job\JobInterface $job Job to initialize this task for.
     *
     * @return void
     */
    public function init(\Xinc\Core\Job\JobInterface $job);

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate();

    /**
     * Process the task
     *
     * @param Xinc\Core\Job\JobInterface $job Job to process this task for.
     *
     * @return void
     */
    public function process(\Xinc\Core\Job\JobInterface $job);

    /**
     * Returns name of task.
     *
     * @return string Name of task.
     */
    public function getName();

    /**
     * Returns the slot of this task inside a build.
     *
     * @return integer The slot number.
     * @see Xinc/Plugin/Slot.php for available slots
     */
    public function getPluginSlot();

    /**
     * Support for subtasks, empty by default.
     *
     * @param TaskInterface $task Task to register
     *
     * @return void
     */
    public function registerTask(TaskInterface $task);

    /**
     * Gets registered subtask for this task.
     *
     * @return Xinc_Build_Tasks_Iterator
     */
    public function getTasks();

    public function getXml();
    public function setXml(\SimpleXMLElement $element);
}
