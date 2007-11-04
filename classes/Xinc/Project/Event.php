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

class Xinc_Project_Event
{
    
    /**
     * Daemon is initialized
     *
     */
    const GLOBAL_INIT_START=0;
    /**
     * Daemon initialization is done
     *
     */
    const GLOBAL_INIT_DONE=1;
    /**
     * Project is intialized
     *
     */
    const PROJECT_INIT_START=2; // Project loaded
    /*
     * Project initialization is done
     */
    const PROJECT_INIT_DONE=3;
    const INFO=4;
    const WARNING=5;
    const DEBUG=6;
    const EXCEPTION=7;
    const ERROR=8;
    const PRE_PROCESS_START=10; // 
    const PRE_PROCESS_DONE=11;
    const PROCESS_START=20;
    const PROCESS_DONE=21;
    const POST_PROCESS_START=30;
    const POST_PROCESS_DONE=31;
    
    private $_slot;
    private $_event;
    private $_status;
    
    public function __construct($slot,$event,$status)
    {
        $this->_slot=$slot;
        $this->_event=$event;
        $this->_status=$status;
    }
    
    public function getEvent()
    {
        return $this->_event;
    }
    
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function getSlot()
    {
        return $this->_slot;
    }
    
}