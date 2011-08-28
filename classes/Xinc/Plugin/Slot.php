<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Definition of Plugin Slots
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
 * @link      http://xincplus.sourceforge.net
 */

class Xinc_Plugin_Slot
{
    /**
     * Plugin is loaded when Xinc-Daemon starts running
     */
    const GLOBAL_INIT = 0;

    /**
     * Plugin is loaded when Xinc Daemon starts running
     * and listens globally on all events (across projects)
     */ 
    const GLOBAL_LISTENER = 1; 
    
    /**
     * Plugin is run in any slot (listeners)
     */
    const PROJECT_LISTENER = 2; 
    
    /**
     * Project is initialized when starting up Xinc daemon
     */
    const PROJECT_INIT = 3;
    
    
    const PROJECT_SET_VALUES = 4;
    
    const INIT_PROCESS = 5;

    /**
     * First step, ModificiationSets, BootStrappers etc
     */
    const PRE_PROCESS = 10;

    /**
     * Builders
     */
    const PROCESS = 20;

    /**
     * Publishers
     */
    const POST_PROCESS = 30; 
    
    const SUBTASK = 40;
}