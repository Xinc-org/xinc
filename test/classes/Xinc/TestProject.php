<?php
/**
 * Test Class for the Xinc Build Properties
 * 
 * @package Xinc.Project
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

require_once 'Xinc/Project.php';



require_once 'Xinc/BaseTest.php';

class Xinc_TestProject extends Xinc_BaseTest
{
    
   
    public function testSetters()
    {
        $project = new Xinc_Project();
        $name = 'test ' . rand(21213,123213);
        $project->setName($name);
        
        $this->assertEquals($name, $project->getName(), 'Names should match');
        
        $status = Xinc_Project_Status::MISCONFIGURED;
        $project->setStatus($status);
        
        $this->assertEquals($status, $project->getStatus(), 'Stati should match');
    }
    public function testLogging()
    {
        $project = new Xinc_Project();
        $name = 'test ' . rand(21213, 123213);
        $project->setName($name);
        $message = 'info ' . rand(3123123, 123123213);
        $project->info($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        $message = 'debug ' . rand(3123123, 123123213);
        $project->debug($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        $message = 'warn ' . rand(3123123, 123123213);
        $project->warn($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        $message = 'error ' . rand(3123123, 123123213);
        $project->error($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        Xinc_Logger::getInstance()->setLogLevel(Xinc_Logger::LOG_LEVEL_VERBOSE);
        $message = 'verbose ' . rand(3123123, 123123213);
        ob_start();
        $project->verbose($message);
        $contents = ob_get_clean();
        $lastMsg = Xinc_StreamLogger::getLastLogMessage();
        Xinc_Logger::getInstance()->setLogLevel(Xinc_Logger::LOG_LEVEL_DEBUG);
        $this->assertTrue(strpos($lastMsg, $message) !== false,
                         'Last message should contain message');
        //$this->assertEquals($lastMsg, $contents,
        //                 'Last message should contain message');
        
        
    }   
}