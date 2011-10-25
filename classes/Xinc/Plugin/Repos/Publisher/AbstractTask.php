<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Publisher
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

require_once 'Xinc/Plugin/Task/Base.php';

abstract class Xinc_Plugin_Repos_Publisher_AbstractTask extends Xinc_Plugin_Task_Base
{

    public final function process(Xinc_Build_Interface $build)
    {
        
        if ( ($status = $this->publish($build)) === true ) {
            
            $build->setStatus(Xinc_Build_Interface::PASSED);
        } else if ( $status == -1 ) {
            $build->setStatus(Xinc_Build_Interface::STOPPED);
        } else {
            $build->setStatus(Xinc_Build_Interface::FAILED);
        }
        
    }
    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::POST_PROCESS;
    }
    public function validate()
    {
        try {
            return $this->validateTask();
        }
        catch(Exception $e){
            Xinc_Logger::getInstance()->error('Could not validate: '
                                             . $e->getMessage());
            return false;
        }
    }
    public function registerTask(Xinc_Plugin_Task_Interface $task)
    {
        Xinc_Logger::getInstance()->debug('Registering Subtask: ' . get_class($task));
        $this->_subtasks[] = $task;
    }
    public abstract function validateTask();
    public abstract function publish(Xinc_Build_Interface $build);
}