<?php
/**
 * Test Class for the Cron Scheduler Task
 * 
 * @package Xinc.Plugin
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
require_once 'Xinc/Plugin/Repos/Schedule.php';
require_once 'Xinc/Plugin/Repos/Cron/Task.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Plugin_Repos_Cron_TestTask extends Xinc_BaseTest
{
    
   
    public function testMinutes()
    {
        $task = new Xinc_Plugin_Repos_Cron_Task(new Xinc_Plugin_Repos_Schedule());
        $task->setTimer('*/2 * * * *');
        $nextTime = $task->getTimeFromCron(time());
        $this->assertTrue($nextTime-time()<=239, 'Build time should be within 239 seconds');
        
        $nowMinute = date('i');
        if ($nowMinute>1) {
            $nowMinute--;
            $task = new Xinc_Plugin_Repos_Cron_Task(new Xinc_Plugin_Repos_Schedule());
            $task->setTimer($nowMinute . ' * * * *');
            $nextTime = $task->getTimeFromCron(time());
            $this->assertTrue($nextTime-time()<3600, 'Build time should be within less than 1 hour');
            $this->assertTrue($nextTime-time()>60, 'Build time should be within more than 1 minute');
        }
        
    }

    public function testHours()
    {
        $task = new Xinc_Plugin_Repos_Cron_Task(new Xinc_Plugin_Repos_Schedule());
       
        $now = time();
        $nowHour = date('H', $now);
        $nowMinute = date('i', $now);
        $nowHour++;
        $task = new Xinc_Plugin_Repos_Cron_Task(new Xinc_Plugin_Repos_Schedule());
        $task->setTimer($nowMinute . ' ' . $nowHour . ' * * *');
        $nextTime = $task->getTimeFromCron($now);
        $this->assertTrue($nextTime-$now<=3600, 'Build time should be in 1 hour but is: '
                                                . date('Y-m-d H:i:s', $nextTime) . '<->' 
                                                . date('Y-m-d H:i:s', $now));
        $this->assertTrue($nextTime-$now>=3540, 'Build time should be in 1 hour but is: '
                                                . date('Y-m-d H:i:s', $nextTime) . '<->' 
                                                . date('Y-m-d H:i:s', $now));
        
    
        
    }
    
    public function testIssue154()
    {
        $task = new Xinc_Plugin_Repos_Cron_Task(new Xinc_Plugin_Repos_Schedule());
        $task->setTimer('*/4 * * * *');
        $time = strtotime('2008-03-31 23:57:28');
        $nextTime = $task->getTimeFromCron($time);
        $this->assertTrue($nextTime-time()<=240, 'Build time should be within 240 seconds');
        
    }
    
    public function testLeapYear()
    {
        $task = new Xinc_Plugin_Repos_Cron_Task(new Xinc_Plugin_Repos_Schedule());
        $task->setTimer('*/5 * * * *');
        $time = mktime(23,55,1,2,27,2008);
        $compareTime = mktime(0,0,0,2,28,2008);
        $nextTime = $task->getTimeFromCron($time);
        $this->assertTrue($nextTime-$time<=300, 'Build time should be within 300 seconds but is: ' 
                                              . $nextTime . '=>'
                                              . date('Y-m-d H:i:s', $nextTime));
        $this->assertEquals($nextTime, $compareTime, 'Next build time should be comparetime');
    }
   
}