<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * This interface represents a publishing mechanism to publish build results
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

require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Plugin_Repos_Publisher_OnSuccess_Task
    extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
    /**
     * Returns name of task.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return 'onsuccess';
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validateTask()
    {
        
        foreach ( $this->arSubtasks as $task ) {
            if (!$task instanceof Xinc_Plugin_Repos_Publisher_AbstractTask) {
                return false;
            }
                
        }
        return true;
    }

    public function publish(Xinc_Build_Interface $build)
    {
        /**
         * We only process on success. 
         * Failed builds are not processed by this publisher
         */
        if ($build->getStatus() != Xinc_Build_Interface::PASSED ) return;
        
        $published = false;
        $build->info('Publishing with OnSuccess Publishers');
        foreach ($this->arSubtasks as $task) {
            $published = true;
            $build->info('Publishing with OnSuccess Publisher: ' . get_class($task));
            $task->publish($build);
            if ($build->getStatus() != Xinc_Build_Interface::PASSED) {
                $build->error('Error while publishing on Success. OnSuccess-Publish-Process stopped');
                break;
            }
        }
        if (!$published) {
            $build->info('No Publishers registered OnSuccess');
        }
    }
}