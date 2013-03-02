<?php
/**
 * Test Class for the Xinc Logge
 * 
 * @package Xinc.Logger
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
require_once 'Xinc/Logger.php';

require_once 'Xinc/BaseTest.php';

class Xinc_TestLogger extends Xinc_BaseTest
{
    
    public function setUp()
    {
      
        Xinc_Logger::tearDown();
        $this->sharedFixture = Xinc_Logger::getInstance();
        $this->sharedFixture->setLogLevel(Xinc_Logger::LOG_LEVEL_DEBUG);
        try {
            $this->sharedFixture->setXincLogFile("xinclogger://test");
            $this->sharedFixture->setBuildLogFile("xinclogger://test");
        } catch (Exception $e) {
            var_dump($e);
        }
    }
    
    public function testLogLevel()
    {
        $this->sharedFixture->setLogLevel(Xinc_Logger::LOG_LEVEL_DEBUG);
        $this->assertEquals($this->sharedFixture->getLogLevel(), Xinc_Logger::LOG_LEVEL_DEBUG ,
                            'We did not get back what we set, BUT: ' . $this->sharedFixture->getLogLevel());
        
        $this->sharedFixture->setLogLevel(Xinc_Logger::LOG_LEVEL_ERROR);
        $this->assertEquals($this->sharedFixture->getLogLevel(), Xinc_Logger::LOG_LEVEL_ERROR ,
                            'We did not get back what we set, BUT: ' . $this->sharedFixture->getLogLevel());
    }
    
    public function testLogWarn()
    {
       $message = 'Test Debug: '.rand(100,99999999);
       $this->sharedFixture->warn($message);
       $lastMessage = Xinc_StreamLogger::getLastLogMessage();
       
       $this->assertTrue(strpos($lastMessage, $message)!==false, 'Message should contain the last Test');
       $this->assertTrue(strpos($lastMessage, 'warn')!==false, 'Message should contain the debug hint');
       
       $this->sharedFixture->flush();
       
       //Xinc_StreamLogger::getLastLogMessage();
       $lastMessage = Xinc_StreamLogger::getLogMessageFromEnd(1);
       $this->assertTrue(strpos($lastMessage, 'priority="warn"')!==false, 'Message should contain the last Test, but is:' . $prevMessage . ' - ' . $lastMessage);
       
    }
    
    public function testLogError()
    {
       $message = 'Test Debug: '.rand(100,99999999);
       $this->sharedFixture->error($message);
       $lastMessage = Xinc_StreamLogger::getLastLogMessage();
       
       $this->assertTrue(strpos($lastMessage, $message)!==false, 'Message should contain the last Test');
       $this->assertTrue(strpos($lastMessage, 'error')!==false, 'Message should contain the debug hint');
       
       $this->sharedFixture->flush();
       
       $lastMessage = Xinc_StreamLogger::getLogMessageFromEnd(1);
       $this->assertTrue(strpos($lastMessage, 'priority="error"')!==false, 'Message should contain the last Test');
       
    }
    public function testLogDebug()
    {
       $message = 'Test Debug: '.rand(100,99999999);
       $this->sharedFixture->debug($message);
       $lastMessage = Xinc_StreamLogger::getLastLogMessage();
       
       $this->assertTrue(strpos($lastMessage, $message)!==false, 'Message should contain the last Test');
       $this->assertTrue(strpos($lastMessage, 'debug')!==false, 'Message should contain the debug hint');
       
       $this->sharedFixture->flush();
       
       $lastMessage = Xinc_StreamLogger::getLogMessageFromEnd(1);
       $this->assertTrue(strpos($lastMessage, 'priority="debug"')!==false, 'Message should contain the last Test');
       
    }
    
    public function testLogInfo()
    {
       $message = 'Test Debug: '.rand(100,99999999);
       $this->sharedFixture->info($message);
       $lastMessage = Xinc_StreamLogger::getLastLogMessage();
       
       $this->assertTrue(strpos($lastMessage, $message)!==false, 'Message should contain the last Test');
       $this->assertTrue(strpos($lastMessage, 'info')!==false, 'Message should contain the debug hint');
       
       $this->sharedFixture->flush();
       
       $lastMessage = Xinc_StreamLogger::getLogMessageFromEnd(1);
       $this->assertTrue(strpos($lastMessage, 'priority="info"')!==false, 'Message should contain the last Test');
       
    }
    
    
    public function testLogVerbose()
    {
       $message = 'Test Debug: '.rand(100,99999999);
       $this->sharedFixture->setLogLevel(Xinc_Logger::LOG_LEVEL_VERBOSE);
       
       
       $this->sharedFixture->verbose($message);

       
       $lastMessage = Xinc_StreamLogger::getLogMessageFromEnd(0);
       $this->sharedFixture->setLogLevel(Xinc_Logger::LOG_LEVEL_DEBUG);
       
       
       $this->assertTrue(strpos($lastMessage, $message)!==false, 'Message should contain the last Test');
       //$this->assertTrue(strpos($contents, $message)!==false, 'Output Message should contain the last Test');
       $this->assertTrue(strpos($lastMessage, 'verbose')!==false, 'Message should contain the debug hint');
       
       //$this->assertEquals($contents, $lastMessage, 'Verbose output and written message should be the same');
       
       $this->sharedFixture->flush();
       
       $lastMessage = Xinc_StreamLogger::getLogMessageFromEnd(1);
       $this->assertTrue(strpos($lastMessage, 'priority="verbose"')!==false, 'Message should contain the last Test');
       
       $message = 'Test Debug Verbose: '.rand(100,99999999);
       $this->sharedFixture->verbose($message);
       $lastMessage2 = Xinc_StreamLogger::getLogMessageFromEnd(1);
       $this->assertTrue(strpos($lastMessage2, $message)===false, 'Message should not contain the last Test');
       
       $this->assertEquals($lastMessage, $lastMessage2, 'Since we are not verbose anymore the last message '
                                                      . 'should not have been logged');
    }
   
   
}