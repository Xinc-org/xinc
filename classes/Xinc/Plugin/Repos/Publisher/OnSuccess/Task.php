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
require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Plugin_Repos_Publisher_OnSuccess_Task extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
   
    private $_plugin;
    public function getName()
    {
        return 'onsuccess';
    }
    public function validateTask()
    {
        
        foreach ( $this->_subtasks as $task ) {
            if ( !in_array('Xinc_Plugin_Repos_Publisher_AbstractTask', class_parents($task)) ) {
                return false;
            }
                
        }
        return true;
    }
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
    }
    public function publish(Xinc_Project &$project)
    {
        /**
         * We only process on success. 
         * Failed builds are not processed by this publisher
         */
        if ($project->getStatus() != Xinc_Project_Build_Status_Interface::PASSED ) return;
        
        $published=false;
        $project->info('Publishing with OnSuccess Publishers');
        foreach ($this->_subtasks as $task) {
            $published=true;
            $project->info('Publishing with OnSuccess Publisher: '.$task->getClassname());
            $task->publish($project);
            if ($project->getStatus() != Xinc_Project_Build_Status_Interface::PASSED) {
                $project->error('Error while publishing on Success. OnSuccess-Publish-Process stopped');
                break;
            }
        }
        if (!$published) {
            $project->info('No Publishers registered OnSuccess');
        }
    }
}