<?php
/**
 * PUT DESCRIPTION HERE
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
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
require_once 'Xinc/Plugin/Task/Base.php';

class Xinc_Plugin_Repos_Configuration_Task extends Xinc_Plugin_Task_Base
{

    public function getPluginSlot(){
        /**
         * see Xinc/Plugin/Slot.php for available slots
         */
        return Xinc_Plugin_Slot::GLOBAL_INIT;
    }

    public function validate()
    {
        foreach ($this->_subtasks as $task) {
            if (!$task instanceof Xinc_Plugin_Repos_Configuration_AbstractTask ) {
                return false;
            }
        }
        return true;
    }
    public function getName(){
         return 'configuration';
    }

    public function getParentTask()
    {
        return 'xinc';
    }
    
    public function process(Xinc_Build_Interface &$build){
        foreach ($this->_subtasks as $task) {
            $task->configure(Xinc::getInstance());
        }
    }
    
}