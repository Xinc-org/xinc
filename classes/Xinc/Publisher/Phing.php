<?php
/**
 * This class represents a publisher that calls a phing task.  The build process 
 * is activated if the publishOn**** variable matches the buildStatus passed to publishOn.
 * 
 * @package Xinc.Publishers
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
require_once 'Xinc/Publisher/Interface.php';
require_once 'Xinc/Logger.php';

class Xinc_Publisher_Phing implements Xinc_Publisher_Interface
{
	/**
	 * Path to the Phing XML build file. 
	 *
	 * @var string
	 */
	private $buildFile;
	
	/**
	 * Phing target.
	 *
	 * @var string
	 */
  	private $target;
  	
  	/**
	 * Specifies the working directory to execute phing from.
	 *
	 * @var string
	 */
	private $workingDirectory = '.';
  	
  	/**
  	 * Status on which to execute this publisher
  	 *
  	 * @var boolean
  	 */
	private $publishOnSuccess = false;
	private $publishOnFailure = false;
  	
	/**
	 * Set the path to the Phing XML build file.
	 *
	 * @param string $buildFile
	 */
  	public function setBuildFile($buildFile)
  	{	
  		$this->buildFile = $buildFile;
	}
  
	/**
	 * Set the Phing target to be run.
	 *
	 * @param string $target
	 */
  	public function setTarget($target)
  	{
	  	$this->target = $target;
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
  	 * Set whether to publish on a successful build
  	 *
  	 * @param boolean $publishOnSuccess
  	 */
  	public function setPublishOnSuccess($publishOnSuccess)
  	{
  		$this->publishOnSuccess = $publishOnSuccess;
  	}
  	
  	/**
  	 * Set whether to publish on a unsuccessful build
  	 *
  	 * @param boolean $publishOnFailure
  	 */
  	public function setPublishOnFailure($publishOnFailure)
  	{
  		$this->publishOnFailure = $publishOnFailure;
  	}
  	
  	/**
  	 * Given the status of the last build (true/false) this method will return
  	 * a boolean describing whether its publish method should be executed or not.
  	 *
  	 * @param boolean $buildStatus
  	 * @return boolean
  	 */
  	public function publishOn($buildStatus) 
  	{
  	    if ($buildStatus && $this->publishOnSuccess) {
  	        return true;
  	    } elseif (!$buildStatus && $this->publishOnFailure) {
            return true;
  	    } else {
  	        return false;
  	    }
  	}

  	/**
  	 * Publish the build.  (This one uses Phing, but Email and file copies are alternative options).
  	 *
  	 */
  	public function publish() 
  	{
  		Xinc_Logger::getInstance()->info("Executing phing publisher with task '" . $this->target . "'");
	    $phingBuilder = new Xinc_Builder_Phing();
	    $phingBuilder->setBuildFile($this->buildFile);
	    $phingBuilder->setTarget($this->target);
	    if (isset($this->workingDirectory)) {
	        $phingBuilder->setWorkingDirectory($this->workingDirectory);
	    }
	    $phingBuilder->build();
	    unset($phingBuilder);
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