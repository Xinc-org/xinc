<?php
/**
 * The logging singleton.
 * 
 * @package Xinc.Logger
 * @author David Ellis
 * @author Gavin Foster
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
require_once 'Xinc/Logger/Message.php';

class Xinc_Logger
{
    /**
     * Singleton instance variable.
     *
     * @var Xinc_Logger
     */
    private static $_instance;
    
    /**
     * Path to the log file.
     *
     * @var string
     */
    private $_logFile;
        /**
     * Path to the log file.
     *
     * @var string
     */
    private $_buildLogFile;
    /**
     * Queue of logger messages.
     *
     * @var Xinc_Logger_Message[]
     */
    private $_logQueue;
    
    /**
     * Maximum length of log queue (i.e. last $max items will be in the queue).
     *
     * @var integer
     */
    private $_max;
    
    private $_logLevel = 2;
    const LOG_LEVEL_VERBOSE = 0;
    const LOG_LEVEL_DEBUG = 1;
    const LOG_LEVEL_INFO = 2;
    const LOG_LEVEL_WARN = 3;
    const LOG_LEVEL_ERROR = 4;
    
    /**
     * Log levels
     *
     */
    static $logLevelError = array(4, 'error');
    static $logLevelWarn = array(3, 'warn');
    static $logLevelDebug = array(1, 'debug');
    
    static $logLevelInfo = array(2, 'info');
    static $logLevelVerbose = array(0, 'verbose');
    
    
    /**
     * Private singleton constructor.
     *
     */
    private function __construct() 
    {
        $this->_logQueue = array();
        $this->_max = 500;
    }
    public function setLogLevel($level)
    {
        
        $this->_logLevel = $level;
        $this->info("Setting loglevel to $level");
    }
    
    public function getLogLevel()
    {
        return $this->_logLevel;
    }
    /**
     * Singleton getInstance method.
     *
     * @return Xinc_Logger
     */
    public static function getInstance()
    {
        if (!Xinc_Logger::$_instance) {
            Xinc_Logger::$_instance = new Xinc_Logger();
        }
        return Xinc_Logger::$_instance;
    }
    
    /**
     * Add a new log message to the logger queue.
     *
     * @param string $priority
     * @param string $msg
     */
    private function log($priority, $msg)
    {
        /** @todo parse log level to display from a config */
        if ($priority[0] < $this->_logLevel) {
            return;
        }
        
        $logTime = time();
        
        $this->_logQueue[] = new Xinc_Logger_Message($priority[1], $logTime, $msg);
        
        if (count($this->_logQueue)>$this->_max) {
            
            $this->flush();
            
        }

        /** ensure the output messages line up vertically */
        $prioritystr = '[' . $priority[1] . ']';
        $timestr = '[' . date('Y-m-d H:i:s', $logTime) . ']';
        while (strlen($prioritystr) < 7) {
            $prioritystr .= ' ';
        }
        $message = ' ' . $prioritystr . '  ' . $timestr . ' ' . $msg."\n";
        if ($this->_logLevel == self::LOG_LEVEL_VERBOSE) fputs(STDERR, $message);
        if ($this->_logFile != null) {
            error_log($message, 3, $this->_logFile);
        }
    }
    
    /**
     * Log a message with priority 'error'.
     *
     * @param string $msg
     */
    public function error($msg)
    {
        $this->log(self::$logLevelError, $msg);
    }
    
    /**
     * Log a message with priority 'warn'.
     *
     * @param string $msg
     */
    public function warn($msg)
    {
        $this->log(self::$logLevelWarn, $msg);
    }
    
    /**
     * Log a message with priority 'info'.
     *
     * @param string $msg
     */
    public function info($msg)
    {
        $this->log(self::$logLevelInfo, $msg);
    }
    
    /**
     * Log a message with priority 'debug'.
     *
     * @param string $msg
     */
    public function debug($msg)
    {
        $this->log(self::$logLevelDebug, $msg);
    }
    /**
     * Log a message with priority 'verbose'.
     *
     * @param string $msg
     */
    public function verbose($msg)
    {
        $this->log(self::$logLevelVerbose, $msg);
    }    
    /**
     * Flush the log queue to the log file.
     *
     */
    public function flush()
    {
        if ( null == $this->_buildLogFile) {
            $this->_resetLogQueue();
            return;
        }
        
        $this->doc = new DOMDocument();
        $this->doc->formatOutput = true;
    
        $buildElement = $this->doc->createElement('build');
        $this->doc->appendChild($buildElement);
        
        for ($i = count($this->_logQueue)-1; $i >= 0; $i--) {
            $message = $this->_logQueue[$i];
            $messageElement = $this->doc->createElement('message', $message->message);
            $messageElement->setAttribute('priority', $message->priority);
            $messageElement->setAttribute('timestamp', $message->timestamp);
            $messageElement->setAttribute('time', date('Y-m-d H:i:s', $message->timestamp));
            $buildElement->appendChild($messageElement);
        }
        $this->info('Flushing log to: ' . $this->_buildLogFile);
        
        file_put_contents($this->_buildLogFile, $this->doc->saveXML());
        
        $this->_resetLogQueue();
    }
    
    private function _resetLogQueue()
    {
        $this->_logQueue = array();
    }
    
    /**
     * Set the path to the log file.
     *
     * @param string $logFile
     */
    public function setBuildLogFile($logFile)
    {
        $this->_buildLogFile = $logFile;
    }
    
    public function setXincLogFile($logFile)
    {
        $this->_logFile = $logFile;
    }
    
    public static function tearDown()
    {
        self::$_instance = null;
    }
}
