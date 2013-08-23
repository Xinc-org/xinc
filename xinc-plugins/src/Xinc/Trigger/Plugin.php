<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Plugin
 * @subpackage Trigger
 * @author     Arno Schneider <username@example.org>
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2007 Arno Schneider, Barcelona
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *             This file is part of Xinc.
 *             Xinc is free software; you can redistribute it and/or modify
 *             it under the terms of the GNU Lesser General Public License as
 *             published by the Free Software Foundation; either version 2.1 of
 *             the License, or (at your option) any later version.
 *
 *             Xinc is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Lesser General Public License for more details.
 *
 *             You should have received a copy of the GNU Lesser General Public
 *             License along with Xinc, write to the Free Software Foundation,
 *             Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link       http://code.google.com/p/xinc/
 */

require_once 'Xinc/Plugin/Abstract.php';

require_once 'Xinc/Trigger/Task.php';
require_once 'Xinc/Trigger/Task/Cron.php';
require_once 'Xinc/Trigger/Task/Scheduler.php';
require_once 'Xinc/Trigger/Task/Sensor.php';

class Xinc_Trigger_Plugin extends Xinc_Plugin_Abstract
{
    /**
     * Returns the defined tasks of the plugin
     *
     * @return Xinc_Plugin_Task[]
     */
    public function getTaskDefinitions()
    {
        return array(
            new Xinc_Trigger_Task($this),
            new Xinc_Trigger_Task_Cron($this),
            new Xinc_Trigger_Task_Scheduler($this),
            new Xinc_Trigger_Task_Sensor($this),
        );
    }
}
