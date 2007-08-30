<?php
/**
 * This class executes the phing build task .
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
require_once 'phing/Phing.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Builder/Interface.php';

class Xinc_Builder_Phing implements Xinc_Builder_Interface
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
	 * Sets the build file to execute.
	 *
	 * @param string $buildFile
	 */
	public function setBuildFile($buildFile)
	{
		$this->buildFile = $buildFile;
	}

	/**
	 * Sets the build's working directory.
	 * 
	 * @param string $workingDirectory
	 */
	public function setWorkingDirectory($workingDirectory)
	{
		$this->workingDirectory = $workingDirectory;
	}

	/**
	 * Executes the build, returning whether it was successful.
	 *
	 * @param string
	 * @return boolean
	 */
	public function build() 
	{
	  	$output = array();
	  	$cwd = getcwd();
	  	
	  	chdir($this->workingDirectory);
	  	Xinc_Logger::getInstance()->debug("Phing changing directory to ".$this->workingDirectory);
	  	
	  	exec("phing  -f $this->buildFile $this->target", $output);
	  	Xinc_Logger::getInstance()->debug("Phing executing 'phing  -f $this->buildFile $this->target'");
	  	
	  	$resultText = implode("\n", $output);
	  	chdir($cwd);

		if (preg_match('/BUILD FINISHED/', $resultText)) {
			return true;
		} else {
		    return false;
		}
	}
	
	/**
	 * Check necessary variables are set
	 *
	 * @throws Xinc_Exception_MalformedConfig
	 */
	public function validate()
	{
	    if (!isset($this->buildFile)) {
  	        throw new Xinc_Exception_MalformedConfig('Element publisher/phing - required attribute \'buildFile\' is not set');
  	    }
  	    if (!isset($this->target)) {
  	        throw new Xinc_Exception_MalformedConfig('Element publisher/phing - required attribute \'target\' is not set');
  	    }
	}
}