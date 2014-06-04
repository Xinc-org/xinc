<?php
/**
 * Xinc - Continuous Integration.
 * The logging singleton.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
 * @author    David Ellis <username@example.com>
 * @author    Gavin Foster <username@example.com>
 * @author    Arno Schneider <username@example.com>
 * @copyright 2007 David Ellis, One Degree Square
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Core;

class Logger
{
    /**
     * Singleton instance variable.
     *
     * @var Xinc\Core\Logger
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

    const DEFAULT_LOG_LEVEL = 2;

    /**
     * Log levels
     *
     */
    static $logLevelError = array(4, 'error');
    static $logLevelWarn = array(3, 'warn');
    static $logLevelDebug = array(1, 'debug');

    static $logLevelInfo = array(2, 'info');
    static $logLevelVerbose = array(0, 'verbose');

    private $_logLevelSet = false;

    /**
     * Private singleton constructor.
     *
     */
    private function __construct()
    {
        $this->_logQueue = array();
        $this->_max = 50;
    }

    public function setLogLevel($level)
    {
        $this->_logLevelSet = true;
        /**
         * setting to info, so the loglevel change gets written to the log
         */
        $this->_logLevel = self::LOG_LEVEL_INFO;
        $this->info("Setting loglevel to $level");
        $this->_logLevel = $level;
    }

    public function logLevelSet()
    {
        return $this->_logLevelSet;
    }

    public function getLogLevel()
    {
        return $this->_logLevel;
    }

    /**
     * Singleton getInstance method.
     *
     * @return Xinc\Core\Logger
     */
    public static function getInstance()
    {
        if (!Logger::$_instance) {
            Logger::$_instance = new Logger();
        }
        return Logger::$_instance;
    }

    /**
     * Add a new log message to the logger queue.
     *
     * @param string $priority
     * @param string $msg
     * @param resource $fileHandle to write to instead of logfile
     */
    private function log($priority, $msg, $fileHandle = null)
    {
        /** @todo parse log level to display from a config */
        if ($priority[0] < $this->_logLevel && $fileHandle === null) {
            return;
        }

        $logTime = time();

        $this->_logQueue[] = new Logger\Message($priority[1], $logTime, $msg);

        /** ensure the output messages line up vertically */
        $prioritystr = '[' . $priority[1] . ']';
        $timestr = '[' . date('Y-m-d H:i:s - T', $logTime) . ']';
        while (strlen($prioritystr) < 7) {
            $prioritystr .= ' ';
        }
        $message = ' ' . $prioritystr . '  ' . $timestr . ' ' . $msg."\n";

        if ($this->_logLevel == self::LOG_LEVEL_VERBOSE) {
            if (defined('STDERR')) {
                fputs(STDERR, $message);
            } else {
                echo '<!-- LogMessage: ' . $message . " -->\n";
            }
        }

        if ($this->_logFile != null) {
            if ($fileHandle !== null) {
                fputs($fileHandle, $message);
            } else {
                error_log($message, 3, $this->_logFile);
            }
        } else if ($fileHandle !== null) {
            fputs($fileHandle, $message);
        }

        if (count($this->_logQueue)>$this->_max) {
            $this->flush();
        }
    }

    /**
     * Log a message with priority 'error'.
     *
     * @param string $msg
     * @param resource fileHandle
     */
    public function error($msg, $fileHandle = null)
    {
        $this->log(self::$logLevelError, $msg, $fileHandle);
    }

    /**
     * Log a message with priority 'warn'.
     *
     * @param string $msg
     * @param resource fileHandle
     */
    public function warn($msg, $fileHandle = null)
    {
        $this->log(self::$logLevelWarn, $msg, $fileHandle = null);
    }

    /**
     * Log a message with priority 'info'.
     *
     * @param string $msg
     * @param resource fileHandle
     */
    public function info($msg, $fileHandle = null)
    {
        $this->log(self::$logLevelInfo, $msg, $fileHandle = null);
    }

    /**
     * Log a message with priority 'debug'.
     *
     * @param string $msg
     * @param resource fileHandle
     */
    public function debug($msg, $fileHandle = null)
    {
        $this->log(self::$logLevelDebug, $msg, $fileHandle);
    }

    /**
     * Log a message with priority 'verbose'.
     *
     * @param string $msg
     * @param resource fileHandle
     */
    public function verbose($msg, $fileHandle = null)
    {
        $this->log(self::$logLevelVerbose, $msg, $fileHandle = null);
    }

    /**
     * Empty the log queue
     *
     */
    public function emptyLogQueue()
    {
        $this->_resetLogQueue();
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
        $messageElements = array();
        for ($i = count($this->_logQueue)-1; $i >= 0; $i--) {
            $message = $this->_logQueue[$i];
            $messageString  = '<message priority="' . $message->priority . '" ';
            $messageString .= 'timestamp="' . $message->timestamp . '" ';
            $messageString .= 'time="' . date('Y-m-d H:i:s - T', $message->timestamp) . '"><![CDATA[';
            $messageString .= base64_encode($message->message);
            $messageString .= ']]></message>';

            $messageElements[] = $messageString;
        }

        $previousLogMessages = '';

        $dirName = dirname($this->_buildLogFile);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0755, true);
        }
        if (file_exists($this->_buildLogFile)) {
            // copying to temporary file for later inclusion via fgets, less memory consuming
            copy($this->_buildLogFile, $this->_buildLogFile . '.temp');
        }
        $fh = fopen($this->_buildLogFile, 'w+');
        if (is_resource($fh)) {
            fputs($fh, '<?xml version="1.0"?>');
            fputs($fh, "\n");
            fputs($fh, '<build>');
            fputs($fh, "\n");
            fputs($fh, implode("\n", $messageElements));
            fputs($fh, "\n");
            if (file_exists($this->_buildLogFile . '.temp')) {
                $fht = fopen($this->_buildLogFile . '.temp', 'r');
                if (is_resource($fht)) {
                    $lineCounter = 0;
                    while ($line = fgets($fht)) {
                        // skip first two lines (xml decl and build opening element
                        if ($lineCounter>2) {
                            fputs($fh, $line);
                        }
                        $lineCounter++;
                    }
                    fclose($fht);
                    unlink($this->_buildLogFile . '.temp');
                } else {
                    self::error('Cannot include previous log messages');
                }
            } else {
                fputs($fh, '</build>');
            }
            //fputs($fh, $previousLogMessages);
            fclose($fh);
            //file_put_contents($this->_buildLogFile, $buildXml);
        } else {
            self::error('Cannot open: ' . $this->_buildLogFile . ' for writing.');
        }
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

    /**
     * @param string $logFile
     * @throws Xinc\Core\Logger\Exception\NonWriteable
     */
    public function setXincLogFile($logFile)
    {
        $parentDir = dirname($logFile);

        if (!is_writeable($logFile) && !is_writeable($parentDir)) {
            $this->error('Cannot open "' . $logFile . '" for writing', STDERR);
            throw new Logger\Exception\NonWriteableException($logFile);
        }
        $this->_logFile = $logFile;
    }

    public static function tearDown()
    {
        self::$_instance = null;
    }
}
