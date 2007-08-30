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

require_once 'Xinc/Parser.php';
require_once 'Xinc/Exception/MalformedConfig.php';
require_once 'Xinc/Logger.php';

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Xinc_TestParser extends  PHPUnit_Framework_TestCase
{
	private $parser;

	function setUp() 
	{
		$this->parser = new Xinc_Parser();
	}
	
	function tearDown() 
	{		
	}
	
	function testSetConfigFileOK()
	{
		$project = $this->parser->parse("test/resources/configOK.xml");		
		$this->assertTrue( $project[0]->getName()=="Project Name",
		'checking project name matches');
		$this->assertTrue( $project[0]->getInterval()==60,
		'checking the project has the correct interval');
	}
	
	function testSetConfigFileMalformed()
	{
		try {
			error_reporting(0);
			$project = $this->parser->parse("test/resources/configMalformed.xml");		
		}
		catch(Xinc_Exception_MalformedConfig $exception)	{
			error_reporting(E_ALL);
			return;
		}
		$this->fail("expected MalformedConfig to be thrown");
	}
}