<?php
/**
 *	
 * This interface represents some source resource to detect changes in.
 * 
 * 
 * 
 * @package Xinc
 * @author  David Ellis
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

class Xinc_Message 
{
	var $priority;
	var $message;
	
	function __construct($priority, $message)
	{
		$this->priority = $priority;
		$this->message = $message;
	}
	
}


class Xinc_Logger 
{
	private $doc;
	private $logFile;
	
	private $logQueue;
	
	private $max;
	
	public function __construct() 
	{
		$this->logFile = "xinc_log.xml";
	
		$this->logQueue = array();
		
		$this->max = 20;
	}
	
	private function log($priority, $msg)
	{
	
		$this->logQueue[] = new Xinc_Message($priority, time() ."::" . $msg);
		
		if (count($this->logQueue)>$this->max)
			array_shift($this->logQueue);
			
		echo " [$priority]  $msg\n";

	}
	
	public function error($msg)
	{
		$this->log("error", $msg);
	}
	
	public function info($msg)
	{
		$this->log("info", $msg);
	}
	
	public function debug($msg)
	{
		$this->log("debug", $msg);
	}
	
	public function flush()
	{
		
		$this->doc = new DOMDocument();
		$this->doc->formatOutput = true;
	
		$buildElement = $this->doc->createElement("build");
		$this->doc->appendChild($buildElement);
		
		
		for($i=count($this->logQueue)-1;$i>=0; $i--) {
//		foreach ($this->logQueue as $message) {

			$message = $this->logQueue[$i];
			$messageElement = $this->doc->createElement("message",$message->message);
			$messageElement->setAttribute("priority", $message->priority);
			$buildElement->appendChild($messageElement);
		}
		
		file_put_contents($this->logFile,
							$this->doc->saveXML());
	}
	
}


?>