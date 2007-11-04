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

require_once 'Xinc/Plugin/Task/Base.php';
require_once 'Xinc/Project/Build/Scheduler/Interface.php';

class Xinc_Plugin_Repos_Schedule_Task extends Xinc_Plugin_Task_Base implements Xinc_Project_Build_Scheduler_Interface
{
    
    private $_interval;
    private $_plugin;
    private $_project;
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
    }
    public function process(Xinc_Project &$project)
    {
        if (!isset($this->_project)) {
            $project->setScheduler($this);
            $this->_project = $project;
            if (time() < $this->getNextBuildTime()) {
                $this->_project->setStatus(Xinc_Project_Build_Status_Interface::STOPPED);
            }
        }
        
    }
    public function setInterval($interval)
    {
        $this->_interval = $interval;
    }
    
    public function getInterval()
    {
        return $this->_interval;
    }
    
    public function registerTask(Xinc_Plugin_Task_Interface &$task)
    {
        
    }
    
    public function setLastBuildTime($time)
    {
        
    }
    
    public function getNextBuildTime()
    {
        
        $lastBuild = $this->_project->getBuildStatus()->getBuildTime();
        
        if ($lastBuild != null ) {
            $nextBuild = $this->getInterval() + $lastBuild;
        } else {
            // never ran, schedule for now
            $nextBuild = time()-1;
        }
        $this->_project->debug('getNextBuildTime '
                              . ': lastbuild: ' 
                              . date('Y-m-d H:i:s', $lastBuild) 
                              . ' nextbuild: ' 
                              . date('Y-m-d H:i:s', $nextBuild).'');
        return $nextBuild;
    }
    public function getBuildSlot()
    {
        return Xinc_Plugin_Slot::INIT_PROCESS;
    }
    public function validate()
    {
        return $this->_interval > 0;
    }
    public function getName()
    {
        return 'schedule';
    }
}