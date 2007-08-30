<?php
/**
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

require_once 'Xinc/Logger.php';
require_once 'Xinc/Builder/Phing.php';

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Xinc_Builder_TestPhing extends  PHPUnit_Framework_TestCase
{
	private $phingBuilder;
	private $name;
	
	
	function setUp() 
	{
		$this->phingBuilder = new Xinc_Builder_Phing();
	}
	
	function tearDown() 
	{
		
	}
	
	function testBuildFileError() 
	{
		$this->phingBuilder->setBuildFile("test/resources/testBuildError.xml");
		$this->phingBuilder->setTarget("build");
		$result = $this->phingBuilder->build();
		$this->assertFalse($result, "testing failed build");
	}
	
	function testBuildFail() 
	{
		$this->phingBuilder->setBuildFile("test/resources/testBuildFail.xml");
		$this->phingBuilder->setTarget("build");
		$result = $this->phingBuilder->build();
		$this->assertFalse($result, "testing failed build");
	}
	
	function testBuildPass() 
	{
		$this->phingBuilder->setBuildFile("test/resources/testBuildPass.xml");
		$this->phingBuilder->setTarget("build");
		$result = $this->phingBuilder->build();
		$this->assertTrue($result, "testing passed build");
	}
	
	/**
	 * @todo - if the file doesn't exist should we throw an exception as there is no chance
	 *         of a successful build!?
	 */
	function testBuildNonExistentBuildFile() 
	{
		$this->phingBuilder->setBuildFile("examples/notExists.xml");
		$this->phingBuilder->setTarget("build");
		$result = $this->phingBuilder->build();
		$this->assertFalse($result, "testing file exists when it shouldnt.");
	}
	
	/**
	 * tests to see if it works in a different working directory.
	 */
	function testBuildAlternateWorkingDir() 
	{
		$this->phingBuilder->setBuildFile("../test/resources/testBuildPass.xml");
		$this->phingBuilder->setTarget("build");
		$this->phingBuilder->setWorkingDirectory("examples");
		$result = $this->phingBuilder->build();
		$this->assertTrue($result, "testing building in a different working directory");
	}
	
}