<?php
/**
 * Test Class for the Xinc Build Properties
 * 
 * @package Xinc.Build
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
require_once 'Xinc.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Engine/Sunrise.php';
require_once 'Xinc/BaseTest.php';

class Xinc_TestBuild extends Xinc_BaseTest
{
    
    public function testSetters()
    {
        $project = new Xinc_Project();
        $project->setName('test');
        
        $engine = new Xinc_Engine_Sunrise();
        
        $build = new Xinc_Build($engine,$project);
        $status = Xinc_Build_Interface::PASSED;
        $build->setStatus($status);
        
        $this->assertEquals($status, $build->getStatus(), 'Stati should be equal');
        $buildNo = 100;
        $expectedLabel = "BUILD.$buildNo";
        $build->setNumber($buildNo);
        $this->assertEquals($expectedLabel, $build->getLabel(), 'Labels dont match');
        
        $this->assertEquals($buildNo, $build->getNumber(), 'Numbers should equal');
    }
   
    public function testBuildOk()
    {
        $project = new Xinc_Project();
        $project->setName('test');
        
        $engine = new Xinc_Engine_Sunrise();
        
        $build = new Xinc_Build($engine,$project);
        
        
        
        $this->assertEquals($engine, $build->getEngine(), 'Engine should be the same');
        
        
        $actual = $build->getNextBuildTime();
        $now = time();
        
        $this->assertEquals($now, $actual,
                            'Next Buildtimestamps should be equal to the current time');
        
        $buildTimestamp = $now;
        $build->setBuildTime($buildTimestamp);
        
        $build->setStatus(Xinc_Build_Interface::PASSED);
        $build->getProperties()->set('test',1);
        //$workingDir = getcwd();
        //Xinc::getInstance()->setStatusDir($workingDir);
        try {
            $result = $build->serialize();
            $this->assertTrue($result, 'Should serialize successfull');
            
            
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Serialization should not throw an execption');
        }
        
        // test lastBuild
        $lastBuild = $build->getLastBuild();
        
        $this->assertEquals($lastBuild->getProperties()->get('test'), 1, 
                            'Last build should have same property value');
        
        
        
        try {
            $object = Xinc_Build::unserialize($project, $buildTimestamp);
        } catch (Exception $e) {
            $this->assertTrue(false, 'Unserialization should not throw an execption');
        }
        $this->assertEquals($build->getProject()->getName(), $object->getProject()->getName(),
                            'Project Name should have gotten serialized');
        $this->assertEquals($build->getProperties()->get('test'),
                            $object->getProperties()->get('test'),
                           'Properties should be equal');
        $this->assertEquals($build->getStatus(),
                            $object->getStatus(),
                           'statuses should be equal');
    }

    public function testBuildExceptions()
    {
        $project = new Xinc_Project();
        $project->setName('test');
        $build = new Xinc_Build(new Xinc_Engine_Sunrise(),$project);
        $buildTimestamp = time()+1;
        $build->setBuildTime($buildTimestamp);
        $build->setStatus(Xinc_Build_Interface::INITIALIZED);
        $build->getProperties()->set('test',1);
        //$workingDir = getcwd();
        //Xinc::getInstance()->setStatusDir($workingDir);
        try {
            $result = $build->serialize();
            $this->assertTrue(false, 'Should not serialize successfull');
        } catch (Xinc_Build_Exception_NotRun $e) {
            $this->assertTrue(true, 'Serialization should throw an execption');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should not throw this exception');
        }
        try {
            $object = Xinc_Build::unserialize($project, $buildTimestamp);
        } catch (Xinc_Build_Exception_NotFound $pe) {
            $this->assertTrue(true, 'Serialization should throw an execption');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Unserialization should not throw an execption');
        }
       
    }
    
    public function testLogging()
    {
        $project = new Xinc_Project();
        $project->setName('test');
        $build = new Xinc_Build(new Xinc_Engine_Sunrise(),$project);
        $name = 'test ' . rand(21213, 123213);
        $project->setName($name);
        $message = 'info ' . rand(3123123, 123123213);
        $build->info($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        $message = 'debug ' . rand(3123123, 123123213);
        $build->debug($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        $message = 'warn ' . rand(3123123, 123123213);
        $build->warn($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        $message = 'error ' . rand(3123123, 123123213);
        $build->error($message);
        $this->assertTrue(strpos(Xinc_StreamLogger::getLastLogMessage(), $message) !== false,
                         'Last message should contain message');
        
        Xinc_Logger::getInstance()->setLogLevel(Xinc_Logger::LOG_LEVEL_VERBOSE);
        $message = 'verbose ' . rand(3123123, 123123213);
        ob_start();
        $build->verbose($message);
        $contents = ob_get_clean();
        $lastMsg = Xinc_StreamLogger::getLastLogMessage();
        Xinc_Logger::getInstance()->setLogLevel(Xinc_Logger::LOG_LEVEL_DEBUG);
        $this->assertTrue(strpos($lastMsg, $message) !== false,
                         'Last message should contain message');
        //$this->assertEquals($lastMsg, $contents,
        //                 'Last message should contain message');
        
        
    }   
   
}