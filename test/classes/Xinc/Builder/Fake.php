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

require_once 'Xinc/Builder/Phing.php';

class Xinc_Builder_Fake extends Xinc_Builder_Phing
{
	private $success;
	private $buildCalled;
	
	/**
	 * Constructor.
	 */
	function __construct($success) {
		$this->success = $success;
		$this->buildCalled = false;
	}
	
	public function setSuccess($success)	{
		$this->success = $success;
	}
	
	public function setBuildCalled($buildCalled) {
		$this->buildCalled = $buildCalled;
	}
	
	public function getBuildCalled() {
		return $this->buildCalled;
	}
	
	/**
	 * Executes the build, returning whether it was successful.
	 *
	 * @param string
	 * @return boolean
	 */
	function build($target = null) 
	{
		$this->buildCalled = true;
		return $this->success;
	}    
}