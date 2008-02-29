<?php
/**
 * Cron scheduler, allows you to use cron like expression to schedule
 * builds
 * 
 * based on:
 * 
 * pseudo-cron v1.3
 * (c) 2003,2004 Kai Blankenhorn
 * www.bitfolge.de/pseudocron
 * kaib@bitfolge.de
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

require_once 'Xinc/Plugin/Task/Base.php';
require_once 'Xinc/Build/Scheduler/Interface.php';

class Xinc_Plugin_Repos_Cron_Task extends Xinc_Plugin_Task_Base implements Xinc_Build_Scheduler_Interface
{
    
    const PC_MINUTE=1;
    const PC_HOUR=2;
    const PC_DOM=3;
    const PC_MONTH=4;
    const PC_DOW=5;
    const PC_CMD=7;
    const PC_COMMENT=8;
    const PC_CRONLINE=20;

    private $_timer;

    /**
     *
     * @var Xinc_Build_Interface
     */
    private $_build;

    private $_lastBuildTime;
    public function process(Xinc_Build_Interface &$build)
    {
        /**if (!isset($this->_project)) {
         $build->setScheduler($this);
         $this->_build = $build;
         if (time() < $this->getNextBuildTime()) {
         $this->_build->setStatus(Xinc_Build_Interface::STOPPED);
         }
         }*/

    }
    public function setTimer($timer)
    {
        $this->_timer = $timer;
    }


    public function registerTask(Xinc_Plugin_Task_Interface &$task)
    {

    }

    public function setLastBuildTime($time)
    {
        $this->_lastBuildTime = $time;
    }

    public function init(Xinc_Build_Interface &$build)
    {
        $build->setScheduler($this);
    }

    public function getNextBuildTime(Xinc_Build_Interface &$build)
    {
        if ($build->getStatus() == Xinc_Build_Interface::STOPPED) {
            return null;
        }
        //var_dump($build);
        $lastBuild = $build->getLastBuild()->getBuildTime();

        if ($lastBuild == null ) {
            $lastBuild = 0;
        }
        //$nextBuild = $this->getLastScheduledRunTime($this->_timer . ' test',$lastBuild);
        $nextBuild = $this->getTimeFromCron($lastBuild);
        $build->debug('getNextBuildTime '
        . ': lastbuild: '
        . date('Y-m-d H:i:s', $lastBuild)
        . ' nextbuild: '
        . date('Y-m-d H:i:s', $nextBuild).'');
        return $nextBuild;
    }
    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::INIT_PROCESS;
    }
    public function validate()
    {
        $parts = preg_split('/\s+/', $this->_timer);
        return count($parts)==5;
    }
    public function getName()
    {
        return 'cron';
    }



    public function lTrimZeros($number) {
        GLOBAL $debug;
        while ($number[0]=='0') {
            $number = substr($number,1);
        }
        return $number;
    }

    public function incDate(&$dateArr, $amount, $unit) {

        if ($unit=="mday") {
            $dateArr["hours"] = 0;
            $dateArr["minutes"] = 0;
            $dateArr["seconds"] = 0;
            $dateArr["mday"] += $amount;
            $dateArr["wday"] += $amount % 7;
            if ($dateArr["wday"]>6) {
                $dateArr["wday"]-=7;
            }

            
            $months30 = Array(4,6,9,11);
            $months31 = Array(1,3,5,7,8,10,12);
            
            if (date('L') == 1) {
                $months28 = array();
                $months29 = array(2);
            } else {
                $months28 = array(2);
                $months29 = array();
            }
            if (
            (in_array($dateArr["mon"], $months28) && $dateArr["mday"]==28) ||
            (in_array($dateArr["mon"], $months29) && $dateArr["mday"]==29) ||
            (in_array($dateArr["mon"], $months30) && $dateArr["mday"]==30) ||
            (in_array($dateArr["mon"], $months31) && $dateArr["mday"]==31)
            ) {
                $dateArr["mon"]++;
                $dateArr["mday"] = 1;
            }

        } elseif ($unit=="hour") {
            if ($dateArr["hours"]==23) {
                $this->incDate($dateArr, 1, "mday");
            } else {
                $dateArr["minutes"] = 0;
                $dateArr["seconds"] = 0;
                $dateArr["hours"]++;
            }
        } elseif ($unit=="minute") {
            if ($dateArr["minutes"]==59) {
                $this->incDate($dateArr, 1, "hour");
            } else {
                $dateArr["seconds"] = 0;
                $dateArr["minutes"]++;
            }
        }
        //if ($debug) echo sprintf("to %02d.%02d. %02d:%02d\n",$dateArr[mday],$dateArr[mon],$dateArr[hours],$dateArr[minutes]);
    }

    public function parseElement($element, &$targetArray, $numberOfElements) {
        $subelements = explode(",",$element);
        for ($i=0;$i<$numberOfElements;$i++) {
            $targetArray[$i] = $subelements[0]=="*";
        }

        for ($i=0;$i<count($subelements);$i++) {
            if (preg_match("~^(\\*|([0-9]{1,2})(-([0-9]{1,2}))?)(/([0-9]{1,2}))?$~",$subelements[$i],$matches)) {
                if ($matches[1]=="*") {
                    $matches[2] = 0;        // from
                    $matches[4] = $numberOfElements;        //to
                } elseif ($matches[4]=="") {
                    $matches[4] = $matches[2];
                }
                if (isset($matches[5])) {
                    if ($matches[5][0]!="/") {
                        $matches[6] = 1;        // step
                    }
                } else {
                    $matches[6] = 1;
                }
                for ($j=$this->lTrimZeros($matches[2]);$j<=$this->lTrimZeros($matches[4]);$j+=$this->lTrimZeros($matches[6])) {
                    $targetArray[$j] = TRUE;
                }
            }
        }
    }
    public function getTimeFromCron($last)
    {
        if (preg_match("~^([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-7,/*]+|(-|/|Sun|Mon|Tue|Wed|Thu|Fri|Sat)+)\\s+([^#]*)\\s*(#.*)?$~i",$this->_timer . ' test',$job)) {
            
            return $this->getLastScheduledRunTime($job, $last+60);
        } else {
            return false;
        }
    }
    public function getLastScheduledRunTime($job, $last) {

        $extjob = Array();
        $this->parseElement($job[self::PC_MINUTE], $extjob[self::PC_MINUTE], 60);
        $this->parseElement($job[self::PC_HOUR], $extjob[self::PC_HOUR], 24);
        $this->parseElement($job[self::PC_DOM], $extjob[self::PC_DOM], 31);
        $this->parseElement($job[self::PC_MONTH], $extjob[self::PC_MONTH], 12);
        $this->parseElement($job[self::PC_DOW], $extjob[self::PC_DOW], 7);

        $dateArr = getdate($last);
        $minutesAhead = 0;
        while (
        $minutesAhead<525600 AND
        (!$extjob[self::PC_MINUTE][$dateArr["minutes"]] OR
        !$extjob[self::PC_HOUR][$dateArr["hours"]] OR
        (!$extjob[self::PC_DOM][$dateArr["mday"]] OR !$extjob[self::PC_DOW][$dateArr["wday"]]) OR
        !$extjob[self::PC_MONTH][$dateArr["mon"]])
        ) {
            if (!$extjob[self::PC_DOM][$dateArr["mday"]] OR !$extjob[self::PC_DOW][$dateArr["wday"]]) {
                $this->incDate($dateArr,1,"mday");
                $minutesAhead+=1440;
                continue;
            }
            if (!$extjob[self::PC_HOUR][$dateArr["hours"]]) {
                $this->incDate($dateArr,1,"hour");
                $minutesAhead+=60;
                continue;
            }
            if (!$extjob[self::PC_MINUTE][$dateArr["minutes"]]) {
                $this->incDate($dateArr,1,"minute");
                $minutesAhead++;
                continue;
            }
        }
        return mktime($dateArr["hours"],$dateArr["minutes"],0,$dateArr["mon"],$dateArr["mday"],$dateArr["year"]);
    }
}