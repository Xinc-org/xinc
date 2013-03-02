<?php
/**
 * Base test
 * 
 * @package Xinc
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

require_once 'Xinc/StreamLogger.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Xinc_BaseTest extends PHPUnit_Framework_TestCase
{
    public static $registered = false;
    
    public function __construct()
    {
        if (!self::$registered) {
            stream_wrapper_register("xinclogger", "Xinc_StreamLogger")
                                    or die("Failed to register protocol");
            $cwd = dirname(dirname(dirname(__FILE__)));
            $testDir = '"' .$cwd . '"';
            $configFile = '"' . $cwd . '/resources/testSystem.xml"';
            $statusDir = '"'.$cwd . '"';
            $debug = Xinc_Logger::LOG_LEVEL_DEBUG;
            $commandLine = "-s $statusDir -w $testDir -p $testDir -f $configFile -l xinclogger://test -o -v $debug";
            //echo $commandLine;
            Xinc::main($commandLine);
            
            //Xinc::getInstance()->setStatusDir($testDir);
            //Xinc_Logger::getInstance()->setXincLogFile("xinclogger://test");
            //Xinc_Logger::getInstance()->setBuildLogFile("xinclogger://test");
            //Xinc_Logger::getInstance()->setLogLevel(Xinc_Logger::LOG_LEVEL_DEBUG);
            self::$registered=true;
        }
    }
}
