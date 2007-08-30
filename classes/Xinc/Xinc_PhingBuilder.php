<?php
/**
 *	
 * This class executes the phing build task .
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


require_once 'phing/Phing.php';
class Xinc_PhingBuilder 
{
	/**
	 * The name of the build file to execute.
	 *
	 * @var string
	 */
	private $buildFile = "build.xml";
	
	/**
	 * The target within the build file to execute.
	 *
	 * @var string
	 */
	private $target = "build";
	
	
	/**
	 * Specifies the working directory to execute phing from.
	 *
	 * @var string
	 */
	private $workingDirectory = ".";
	
	
	/**
	 * Sets the target to execute.
	 *
	 * @param string $target
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}
	
	/**
	 * Sets the build file to execute
	 *
	 * @param string $buildFile
	 */
	public function setBuildFile($buildFile)
	{
		$this->buildFile = $buildFile;
	}
	
	/**
	 * Sets the builds working directory
	 * @param string $workingDirectory
	 */
	public function setWorkingDirectory($workingDirectory)
	{
		$this->workingDirectory = $workingDirectory;
	}
	
	/**
	 * executes the build.. returns whether it was successful
	 *
	 * @param string $target
	 * @return boolean - build success or failure
	 */
	function build($target =null) 
	{
		global $logger;
	
	  	if ($target==null)  $target = $this->target;
	  	$result = 0;
	  	$output = array();
	  	$cwd = 	getcwd();
	  	chdir($this->workingDirectory);
	  	//print `phing $target`;//, $output, $result);	  	
	  	$ret =  exec("phing  -f $this->buildFile $target",
	  			$output,
	  			$result);
	  	
//	  	$logger->info(implode(" ", $output));
		$logger->info($ret);  	
	  	chdir($cwd);
	  	
	  	return ($result==0);
	}    
}

?>