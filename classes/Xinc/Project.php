<?php
/**
 * This class represents the project to be continuously integrated
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
class Xinc_Project 
{
	/**
	 * The next time this project will be built.
	 *
	 * @var integer
	 */
	private $schedule;
	
	/**
	 * The name of the project.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The interval at which a new build is allowed to take place (seconds).
	 *
	 * @var integer
	 */
  	private $interval;
  
  	/**
  	 * The array of modificationSets to check.
  	 *
  	 * @var Xinc_ModificationSet_Interface[]
  	 */
  	private $modificationSets = array();

	/**
	 * Builder instance
	 * 
	 * @var Xinc_Builder_Interface
	 */
	private $builder;
	
	/**
	 * The array of publishers to notify of build completion.
	 *
	 * @var Xinc_Publisher[]
	 */
  	private $publishers = array();
  	
  	/**
  	 * Indicates the last build status (failed/passed).
  	 * 
  	 * @var boolean 
  	 */
  	private $lastBuildStatus;
  	
  	/**
  	 * Indicates the time that the last build occurred.
  	 * 
  	 */
  	private $lastBuildTime;
  	
  	/**
  	 * Sets the build interval (seconds).
  	 *
  	 * @param integer $interval
  	 */
  	public function setInterval($interval)
  	{
  		$this->interval = $interval;
  	}

  	/**
  	 * Sets the project name for display purposes.
  	 *
  	 * @param string $name
  	 */
  	public function setName($name) 
  	{
  		$this->name = $name;
  	}
 
 	
  	/**
  	 * Adds a modification set to be checked.
  	 *
  	 * @param Xinc_ModificationSet_Interface $modificationSet
  	 */
	function addModificationSet(Xinc_ModificationSet_Interface $modificationSet) 
	{
    	$this->modificationSets[] = $modificationSet;
  	}
	
  	/**
  	 * Adds a publisher to the list.
  	 *
  	 * @param Xinc_Publisher $publisher
  	 */
  	function addPublisher(Xinc_Publisher_Interface $publisher) 
  	{
	    $this->publishers[] = $publisher;
  	}

  	/**
  	 * Sets the builder to use to build the project.
  	 * 
  	 * @param Xinc_Builder_Interface $builder
  	 */
  	function setBuilder(Xinc_Builder_Interface $builder) 
  	{
		$this->builder = $builder;
  	}
  
  	/**
  	 * Returns the time that this project is due to be built.
  	 *
  	 * @return integer
  	 */
  	public function getSchedule() 
  	{
  		return $this->schedule;
  	}
  	
  	/**
  	 * Returns the interval between the next build
  	 * 
  	 * @return integer
  	 */
  	public function getInterval()
  	{
  		return $this->interval;
  	}
  	
  	/**
  	 * Returns this project's name.
  	 *
  	 * @return string
  	 */
  	public function getName()
  	{
  		return $this->name;
  	}
  	
  	/**
  	 * Returns true if a modification set detects a change in the source.
  	 *
  	 * @return boolean
  	 */
	function checkModificationSets() 
	{
    	foreach ($this->modificationSets as $modificationSet) {
      		if ($modificationSet->checkModified()) return true;
    	}
    	return false;
  	}
  
  	/**
  	 * Reschedules the build for now() + interval in the future.
  	 * 
  	 */
  	function reschedule() 
  	{
  	    Xinc_Logger::getInstance()->info('Rescheduling project ' . $this->name . ' for ' . $this->interval);
	    $this->schedule = time() + $this->interval;
  	}  

	/**
	 * builds the project
	 * 
	 * @return boolean - success or failure of the build
	 */
	function build()
	{
		if ($this->builder==null)
			throw new Xinc_Exception_MalformedConfig("Element builder is not defined");

		$this->lastBuildStatus = $this->builder->build();
		$this->lastBuildTime = time();

		return $this->lastBuildStatus;
	}

  	/**
  	 * Executes the publishers (observer design pattern).
  	 * 
  	 */
  	public function publish() 
  	{
    	foreach ($this->publishers as $publisher) {
	      	if ($publisher->publishOn($this->lastBuildStatus)) {
				$publisher->publish();
	      	}
    	}
  	}
  	
  	/**
  	 * when called will serialize the project structure to a disk
  	 * for display to a website..
  	 * 
  	 * @param $dir - the directory to serialize to.
  	 */
  	public function serialize($dir) 
  	{
  		$doc = new DOMDocument();
		$doc->formatOutput = true;

		$projectElement = $doc->createElement('project');

		$doc->appendChild($projectElement);
		
		$messageElement = $doc->createElement('name', $this->name);		
		$projectElement->appendChild($messageElement);
		
		$messageElement = $doc->createElement('buildsuccessful', $this->lastBuildStatus);
		$projectElement->appendChild($messageElement);
		
		$messageElement = $doc->createElement('lastbuildtime', $this->lastBuildTime);
		$projectElement->appendChild($messageElement);
		
		$messageElement = $doc->createElement('schedule', $this->schedule);
		$projectElement->appendChild($messageElement);
		
		$messageElement = $doc->createElement('interval', $this->interval);
		$projectElement->appendChild($messageElement);

		file_put_contents($dir . '/' . $this->name . '.xml', $doc->saveXML());
  	}
}