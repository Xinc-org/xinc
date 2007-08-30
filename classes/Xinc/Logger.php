<?php
/**
 * The logging singleton.
 * 
 * @package Xinc
 * @author David Ellis
 * @author Gavin Foster
 * @version 1.0
 * @copyright 2007 David Ellis, One Degree Square
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *	This file is part of Xinc.
 *	Xinc is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU Lesser General Public License as published by
 *	the Free Software Foundation; either version 2.1 of the License, or
 *	(at your option) any later version.
 *
 *	Xinc is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Lesser General Public License for more details.
 *
 *	You should have received a copy of the GNU Lesser General Public License
 *	along with Xinc, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Logger/Message.php';

class Xinc_Logger 
{
	/**
	 * Singleton instance variable.
	 *
	 * @var Xinc_Logger
	 */
	private static $instance;
	
	/**
	 * Path to the log file.
	 *
	 * @var string
	 */
	private $logFile;
	
	/**
	 * Queue of logger messages.
	 *
	 * @var Xinc_Logger_Message[]
	 */
	private $logQueue;
	
	/**
	 * Maximum length of log queue (i.e. last $max items will be in the queue).
	 *
	 * @var integer
	 */
	private $max;
	
	/**
	 * Log levels
	 *
	 */
	const logLevelError = 'error';
	const logLevelWarn = 'warn';
	const logLevelDebug = 'debug';
	const logLevelInfo = 'info';
	
	
	/**
	 * Private singleton constructor.
	 *
	 */
	private function __construct() 
	{
		$this->logQueue = array();
		$this->max = 20;
	}
	
	/**
	 * Singleton getInstance method.
	 *
	 * @return Xinc_Logger
	 */
	public static function getInstance()
	{
		if (!Xinc_Logger::$instance) {
			Xinc_Logger::$instance = new Xinc_Logger();
		}
		return Xinc_Logger::$instance;
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
		if ($priority == self::logLevelDebug) {
		    //return;
		}
	    
		$this->logQueue[] = new Xinc_Logger_Message($priority, time() ."::" . $msg);
		
		if (count($this->logQueue)>$this->max) {
			array_shift($this->logQueue);
		}

		/** ensure the output messages line up vertically */
		$priority = '[' . $priority . ']';
		while (strlen($priority) < 7) {
		    $priority .= ' ';
		}
			
		echo " $priority $msg\n";
	}
	
	/**
	 * Log a message with priority 'error'.
	 *
	 * @param string $msg
	 */
	public function error($msg)
	{
		$this->log(self::logLevelError, $msg);
	}
	
	/**
	 * Log a message with priority 'warn'.
	 *
	 * @param string $msg
	 */
	public function warn($msg)
	{
		$this->log(self::logLevelWarn, $msg);
	}
	
	/**
	 * Log a message with priority 'info'.
	 *
	 * @param string $msg
	 */
	public function info($msg)
	{
		$this->log(self::logLevelInfo, $msg);
	}
	
	/**
	 * Log a message with priority 'debug'.
	 *
	 * @param string $msg
	 */
	public function debug($msg)
	{
		$this->log(self::logLevelDebug, $msg);
	}
	
	/**
	 * Flush the log queue to the log file.
	 *
	 */
	public function flush()
	{
		$this->doc = new DOMDocument();
		$this->doc->formatOutput = true;
	
		$buildElement = $this->doc->createElement('build');
		$this->doc->appendChild($buildElement);
		
		for ($i=count($this->logQueue)-1;$i>=0; $i--) {
			$message = $this->logQueue[$i];
			$messageElement = $this->doc->createElement('message', $message->message);
			$messageElement->setAttribute('priority', $message->priority);
			$buildElement->appendChild($messageElement);
		}
		
		file_put_contents($this->logFile, $this->doc->saveXML());
	}
	
	/**
	 * Set the path to the log file.
	 *
	 * @param string $logFile
	 */
	public function setLogFile($logFile)
	{
		$this->logFile = $logFile;
	}
}
