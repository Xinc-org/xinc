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


class Xinc_Xinc 
{ 
	
	/**
	 * the projects that Xinc is going build
	 *
	 * @var Xinc_Project[] 
	 */
	private $projects;

	/**
	 * sets the projects to build..
	 *
	 * @param Xinc_Project[] $projects
	 */
	function setProjects($projects)
	{
		$this->projects = $projects;
	}
	
	/**
	 * checks the projects that have been set, and executes the build
	 * if the scheduled time has expired.
	 *
	 */
  	function checkProjects() 
  	{
		global $logger;
  		
    	foreach ($this->projects as $project ) {
	      // if timer has expired..
    		if (time() > $project->getSchedule()) {
   				$logger->info("modification found building project");
    
				if ($project->checkModificationSets()) {

					$logger->info("code not up to date, building project\n");
									
	  				$phingBuilder = $project->phingBuilder;//new PhingBuilder();
	  				$this->buildPassed = $phingBuilder->build();

					if ($this->buildPassed) {
						$logger->info("BUILD PASSED\n");
					}else {
						$logger->warn("BUILD FAILED\n");
					}
					$project->publish();	  
			}
			else {
				print "code up to date, no steps necessary\n";
			}
			$project->reschedule();
    	  }
    	}		
  	}
  	
  	/**
  	 * starts the continuous loop off.
  	 */
	function start() 
	{
	
		global $logger;
		
	    while(true) {
	    	$logger->info("sleeping for 10 seconds");
	    	$logger->flush();
      		sleep(10); // try not to eat too much cpu time in doing nothing..
      		$this->checkProjects();
    	}
  	}
}


?>