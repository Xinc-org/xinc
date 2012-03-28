<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Task
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

require_once 'Xinc/Plugin/Slot.php';
require_once 'Xinc/Plugin/Task/Processor/Interface.php';

interface Xinc_Plugin_Task_Interface
{
    /**
     * Constructor
     */
    public function __construct(Xinc_Plugin_Interface $plugin);
    public function init(Xinc_Build_Interface $build);
    public function process(Xinc_Build_Interface $build);
    public function registerTask(Xinc_Plugin_Task_Interface $task);

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate();

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
     */
    public function getPluginSlot();
    public function getTasks();
    public function getXml();
    public function setXml(SimpleXMLElement $element);
}