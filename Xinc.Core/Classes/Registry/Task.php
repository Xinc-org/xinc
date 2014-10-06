<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
 * @author    Alexander Opitz <opitz.alexander@googlemail.com>
 * @copyright 2014 Alexander Opitz, Leipzig
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

namespace Xinc\Core\Registry;

class Task extends \Xinc\Core\Singleton implements RegistryAbstract
{
    /**
     * @var typeOf The Name of the class this elements should be.
     */
    protected $typeOf = null;

    /**
     * @var array Array of registered elements
     */
    private $slot = array();

    /**
     *
     * @param object $task
     * @throws Xinc\Core\Registry\Exception
     */
    public function register($task)
    {
        parent::register($task->getName(), $task);

        $this->slot[$task->getPluginSlot()][$task->getName()] = $task;
    }

    /**
     *
     * @param object $task
     * @return object
     * @throws Xinc\Core\Registry\Exception
     */
    public function unregister($task)
    {
        unset($this->slot[$task->getPluginSlot()][$task->getName()]);

        return $parent::unregister($task->getName());
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
        if (!isset($this->slot[$slot])) {
            return new \Xinc\Core\Iterator\Task();
        } else {
            return new \Xinc\Core\Iterator\Task($this->slot[$slot]);
        }
    }
}
