<?php
/**
 * This class represents the project to be continuously integrated
 *
 * @package Xinc
 * @author David Ellis
 * @author Gavin Foster
 * @author Arno Schneider
 * @version 1.0
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
require_once 'Xinc/Project/Event.php';
require_once 'Xinc/Plugin/Task/Processor/Interface.php';
require_once 'Xinc/Project/Build/Scheduler/Interface.php';

class Xinc_Project implements Xinc_Plugin_Task_Processor_Interface
{
    
    /**
     * Enter description here...
     *
     * @var Xinc_Project_Build_Status_Interface
     */
    private $_buildStatus;
    
    /**
     * The next time this project will be built.
     *
     * @var Xinc_Project_Build_Scheduler_Interface
     */
    private $_scheduler;
    
    private $_labeler;

    private $_ranOnce=false;
    /**
     * The name of the project.
     *
     * @var string
     */
    private $_name;

    /**
     * The interval at which a new build is allowed to take place (seconds).
     *
     * @var integer
     */
    private $_interval;


    /**
     * Indicates the last build status (failed/passed).
     *
     * @var boolean
     */
    private $_lastBuildStatus;

    /**
     * Indicates the time that the last build occurred.
     *
     */
    private $_lastBuildTime;

    /**
     * Listeners receive every single event of the Xinc-Process
     * and can take action like stopping a built for example
     *
     * @var Xinc_Listener[]
     */
    private $_listeners=array();

    /**
     * Contains tasks that need to be executed for each Process Step
     *
     * @var Xinc_Plugin_Task_Interface[]
     */
    private $_slots=array();

    /**
     * Current status of the project
     *
     * @see Xinc_Project_Status
     * @var integer
     */
    private $_status=1;

    public function setBuildStatus(Xinc_Project_Build_Status_Interface $buildStatus)
    {
        $this->_buildStatus = $buildStatus;
        
        $this->_buildStatus->setProject($this);
    }
    public function setBuildTime($timestamp)
    {
        $this->_buildStatus->setBuildTime($timestamp);
        if($this->_scheduler instanceof Xinc_Project_Build_Scheduler_Interface ) {
            $this->_scheduler->setLastBuildTime($timestamp);
        }
    }
    public function addListener(Xinc_Listener_Interface &$listener)
    {
        $this->_listeners[]=$listener;
    }

    /**
     * Sets the build interval (seconds).
     *
     * @param integer $interval
     */
    public function setInterval($interval)
    {
        $this->_interval = $interval;
    }

    /**
     * Sets the project name for display purposes.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }


    /**
     * Returns the time that this project is due to be built.
     *
     * @return integer
     */
    public function getSchedule()
    {
        $this->debug("Get schedule ");
        if (! $this->_scheduler instanceof Xinc_Project_Build_Scheduler_Interface ) {
            
            if ( ! $this->_ranOnce ) {
                
                $this->_ranOnce = true;
                return 0;
            } else {
                // will not be scheduled
                return time()+1000;
            }
        } else {
            return $this->_scheduler->getNextBuildTime();
        }
    }

    /**
     * Returns the interval between the next build
     *
     * @return integer
     */
    public function getInterval()
    {
        return $this->_interval;
    }

    /**
     * Returns this project's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Reschedules the build for now() + interval in the future.
     *
     */
    public function setScheduler(Xinc_Project_Build_Scheduler_Interface &$scheduler)
    {
        Xinc_Logger::getInstance()->info('Setting scheduler for project ' 
                                        . $scheduler->getName());
        $this->_scheduler = $scheduler;
    }

    public function setBuildLabeler(Xinc_Project_Build_Labeler_Interface &$labeler)
    {
         Xinc_Logger::getInstance()->info('Setting labeler for project ');
        $this->_labeler = $labeler;
    }
    /**
     * Enter description here...
     *
     * @return Xinc_Project_Build_Labeler_Interface
     */
    public function getBuildLabeler()
    {
        return $this->_labeler;
    }
    /**
     * when called will serialize the project structure to a disk
     * for display to a website..
     *
     * @param $dir - the directory to serialize to.
     */
    public function serialize()
    {
        if ( $this->_buildStatus instanceof Xinc_Project_Build_Status_Interface ) {
            $this->_buildStatus->serialize();
        }
    }
    
    public function __destruct()
    {
        $this->serialize();
    }
    

    public function setStatus($status)
    {
        $this->info('Setting status to '.$status);
        $this->_buildStatus->setStatus($status);
    }
    public function getStatus()
    {
        return $this->_buildStatus->getStatus();
    }
    /**
     * 
     *
     * @return Xinc_Project_Build_Status_Interface
     */
    public function getBuildStatus()
    {
        return $this->_buildStatus;
    }
    public function info($message)
    {
        Xinc_Logger::getInstance()->info('[project] ' 
                                        . $this->getName() 
                                        . ': '.$message);
            
    }
    public function debug($message)
    {
        Xinc_Logger::getInstance()->debug('[project] ' 
                                         . $this->getName() 
                                         . ': '.$message);
            
    }
    public function error($message)
    {
        Xinc_Logger::getInstance()->error('[project] ' 
                                         . $this->getName() 
                                         . ': '.$message);
            
    }
    public function process($slot)
    {
        $tasks=$this->getTasksForSlot($slot);
        $event=new Xinc_Project_Event($slot, $slot, $this->getStatus());
        $this->registerEvent($event);
        foreach ($tasks as $task) {

            $task->process($this);

            /**
             * The Post-Process continous on failure
             */
            if ($slot != Xinc_Plugin_Slot::POST_PROCESS) {
                
                if ($this->getStatus() != Xinc_Project_Build_Status_Interface::PASSED) {
                    break;
                }
            }
        }
        $event = new Xinc_Project_Event($slot, $slot+1, $this->getStatus());
        $this->registerEvent($event);
        return $this->getStatus();
    }

    public function registerEvent(Xinc_Project_Event &$event)
    {
        if (!isset($this->_slots[Xinc_Plugin_Slot::PROJECT_LISTENER]) ||
            !is_array($this->_slots[Xinc_Plugin_Slot::PROJECT_LISTENER])) return;
        foreach ( $this->_slots[Xinc_Plugin_Slot::PROJECT_LISTENER] as $listener ) {
            $listener->processEvent($event, $this);
        }
    }
    public function getTasksForSlot($slot)
    {
        if(!isset($this->_slots[$slot])) return array();
        return $this->_slots[$slot];
    }

    public function registerTask(Xinc_Plugin_Task_Interface  &$task)
    {
        $slot=$task->getBuildSlot();
        if(!isset($this->_slots[$slot]))$this->_slots[$slot]=array();
        $this->_slots[$slot][]=$task;
    }
}