<?php
/**
 * First Xinc Engine running on XML
 *  
 * @package Xinc.Engine
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
require_once 'Xinc/Engine/Interface.php';
require_once 'Xinc/Engine/Sunrise/Parser.php';

class Xinc_Engine_Sunrise implements Xinc_Engine_Interface
{
    const NAME = 'Sunrise';
    
    private $_heartBeat;
    
    public function getName()
    {
        return self::NAME;
    }
    public function build(Xinc_Build_Interface &$build)
    {
        
        $buildTime = time();
        $build->setBuildTime($buildTime);
        if ( Xinc_Build_Interface::STOPPED === $build->getStatus() ) {
            
            return;
        }
        /**
         * Increasing the build number, if it fails we need to decrease again
         */
        if ($build->getLastBuild()->getStatus() === Xinc_Build_Interface::PASSED 
            ||
            ($build->getLastBuild()->getStatus() === null &&
             $build->getLastBuild()->getStatus() !== Xinc_Build_Interface::STOPPED)) {
            $build->setNumber($build->getNumber()+1);
            //$this->updateBuildTasks($build);
        }
        $build->updateTasks();
        $build->process(Xinc_Plugin_Slot::INIT_PROCESS);
        if ( Xinc_Build_Interface::STOPPED === $build->getStatus() ) {
            Xinc_Logger::getInstance()->info('Build of Project stopped'
                                             . ' in INIT phase');
            
            //$project->serialize();
            //$build->setBuildTime($buildTime);
            //$build->setStatus(Xinc_Build_Interface::INITIALIZED);
            //Xinc_Logger::getInstance()->setBuildLogFile(null);
            //Xinc_Logger::getInstance()->flush();
            $build->setLastBuild();
            return;
        }                                

        
        
        Xinc_Logger::getInstance()->info("CHECKING PROJECT " 
                                        . $build->getProject()->getName());
        $build->process(Xinc_Plugin_Slot::PRE_PROCESS);
        
        if ( Xinc_Build_Interface::STOPPED === $build->getStatus() ) {
            $build->info("Build of Project stopped, "
                                             . "no build necessary");
            //$build->setBuildTime($buildTime);
            $build->setStatus(Xinc_Build_Interface::INITIALIZED);
            $build->setLastBuild();
            //Xinc_Logger::getInstance()->setBuildLogFile(null);
            //Xinc_Logger::getInstance()->flush();
            return;
        } else if ( Xinc_Build_Interface::FAILED === $build->getStatus() ) {
            //$build->setBuildTime($buildTime);
            $build->updateTasks();
            $build->error("Build failed");
            /**
             * Process failed in the pre-process phase, we need
             * to run post-process to maybe inform about the failed build
             */
            $build->process(Xinc_Plugin_Slot::POST_PROCESS);

        } else if ( Xinc_Build_Interface::PASSED === $build->getStatus() ) {

            $build->info("Code not up to date, "
                                            . "building project");
            //$build->setBuildTime($buildTime);
            
            
            
            $build->updateTasks();
            
            
            $build->process(Xinc_Plugin_Slot::PROCESS);
            if ( Xinc_Build_Interface::PASSED == $build->getStatus() ) {
                
                $build->updateTasks();
                $build->info("BUILD PASSED");
            } else if ( Xinc_Build_Interface::STOPPED == $build->getStatus() ) {
                //$build->setNumber($build->getNumber()-1);
                $build->updateTasks();
                $build->warn("BUILD STOPPED");
            } else if (Xinc_Build_Interface::FAILED == $build->getStatus() ) {
                //if ($build->getLastBuild()->getStatus() == Xinc_Build_Interface::PASSED) {
                //    $build->setNumber($build->getNumber()+1);
                //}
                
                $build->updateTasks();
                $build->error("BUILD FAILED");
            }

            $processingPast = $build->getStatus();
            /**
             * Post-Process is run on Successful and Failed Builds
             */
            $build->process(Xinc_Plugin_Slot::POST_PROCESS);
            
            
            $build->serialize();
            
        } else if ( Xinc_Build_Interface::INITIALIZED === $build->getStatus() ) {
            //$build->setBuildTime($buildTime);
            if ($build->getLastBuild()->getStatus() === null) {
                $build->setNumber($build->getNumber()-1);
            }
            $build->setStatus(Xinc_Build_Interface::STOPPED);
            $build->serialize();
        } else {
            $build->setStatus(Xinc_Build_Interface::STOPPED);
            $build->setLastBuild();
        }
    }
    
    /**
     * Parses Project-Xml and returns
     *
     * @param Xinc_Project_Iterator $projects
     * @return Xinc_Build_Iterator
     * @throws Xinc_Build_Exception_Invalid
     */
    public function parseProjects(Xinc_Project_Iterator $projects)
    {
        $parser = new Xinc_Engine_Sunrise_Parser($this);
        $buildsArr = $parser->parseProjects($projects);
        
        $buildIterator = new Xinc_Build_Iterator($buildsArr);
        
        return $buildIterator;
    }
    
    
    /**
     * returns the interval in seconds in which
     * the engine checks for new builds
     *
     * @return integer
     */
    public function getHeartBeat()
    {
        return $this->_heartBeat;
    }
    
    /**
     * Set the interal in which the engine checks
     * for modified builds, necessary builds etc
     *
     * 
     * see <xinc engine="name" heartbeat="10"/>
     * 
     * @param unknown_type $seconds
     */
    public function setHeartBeat($seconds)
    {
        $this->_heartBeat = $seconds;
    }
    
    /**
     *
     * @return boolean
     */
    public function validate()
    {
        return true;
    }
}