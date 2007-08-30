<?php
/**
 *	
 * This file represents the project to be continuously integrated
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

require_once("Xinc_Publisher.php");

class Xinc_Project 
{
	/**
	 * The next time this project will be build.
	 *
	 * @var int
	 */
	private $schedule;
	
	/**
	 * The name of the project
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The interval at which a new build is allowed to take place.
	 *
	 * @var int
	 */
  	private $interval;
  
  	/**
  	 * The array of modificationSets to check
  	 *
  	 * @var Xinc_ModificationSet[]
  	 */
  	private $modificationSets = array();


	/**
	 * the array of publishers to notify of build completion.
	 *
	 * @var Xinc_Publisher[]
	 */
  	private $publishers = array();
  	
  	/**
  	 * indicates the last build status (failed/passed)
  	 * @var boolean 
  	 */
  	private $buildPassed;
  	

  	/**
  	 * constuctor, schedules first build and sets defaults.
  	 * 
  	 *
  	 * @return Xinc_Project
  	 */
	function Xinc_Project() 
	{
   		$this->reschedule();
    	$this->phingBuild = "build";
  	}

  	/**
  	 * Sets the build interval in seconds
  	 *
  	 * @param int $interval
  	 */
  	public function setInterval($interval)
  	{
  		$this->interval = $interval;
  	}

  	/**
  	 * Sets the project name for display purposes
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
  	 * @param Xinc_ModificationSet $modificationSet
  	 */
	function addModificationSet(Xinc_ModificationSet $modificationSet) 
	{
    	$this->modificationSets[] = $modificationSet;
  	}
	
  	/**
  	 * Adds a publisher to the list..
  	 *
  	 * @param Xinc_Publisher $publisher
  	 */
  	function addPublisher(Xinc_Publisher $publisher) 
  	{
	    $this->publishers[] = $publisher;
  	}

  	/**
  	 * Sets the phing builder to use to build the project..
  	 * @param PhingBuilder $phingBuilder
  	 */
  	function setPhingBuilder(Xinc_PhingBuilder $phingBuilder) 
  	{
		$this->phingBuilder = $phingBuilder;
  	}
  
  	
  	/**
  	 * returns the time that this project is due to be rebuilt
  	 *
  	 * @return int
  	 */
  	public function getSchedule() 
  	{
  		return $this->schedule;
  	}
  	
  	/**
  	 * Returns this projects name..
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
  	 * @return boolean - whether a change has been detected
  	 */
	function checkModificationSets() 
	{
    	foreach ($this->modificationSets as $modificationSet) {
      		if ($modificationSet->checkModified()) return true;
    	}
    	return false;
  	}
  
  	/**
  	 * Reschedules the build for now() + interval in the future..
  	 * @return void
  	 */
  	function reschedule() 
  	{
		print "rescheduling for " . $this->interval . "\n";
	    $this->schedule = time() + $this->interval;
  	}  

  	/**
  	 * Executes the publishers (observer design pattern).
  	 */
  	public function publish() 
  	{
    	foreach ($this->publishers as $publisher) {
	      	if ($publisher->isActive($this->buildPassed)) 
				$publisher->publish();
    	}
  	}
}

?>