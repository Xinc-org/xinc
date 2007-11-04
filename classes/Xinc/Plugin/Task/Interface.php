<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Plugin/Slot.php';
require_once 'Xinc/Project/Status.php';
require_once 'Xinc/Plugin/Task/Processor/Interface.php';

interface Xinc_Plugin_Task_Interface extends Xinc_Plugin_Task_Processor_Interface
{
    /**
     * Returns the slot of the process the plugin is run
     *
     */
    public function getBuildSlot();
        
    public function validate();
    public function getName();
    public function getClassname();
    //public function registerTask(Xinc_Plugin_Task_Interface  &$task);
    public function __construct(Xinc_Plugin_Interface &$plugin);
    public function process(Xinc_Project &$project);
}