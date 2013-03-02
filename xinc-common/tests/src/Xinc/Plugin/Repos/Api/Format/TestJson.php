<?php
/**
 * Test Class for the Xinc Plugin "Property"
 * 
 * @package Xinc.Plugin
 * @author Jeff Carouth <jcarouth@gmail.com>
 * @version 2.0.2
 * @copyright 2008 Jeff Carouth, USA
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
set_include_path(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . PATH_SEPARATOR
	. dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))) . DIRECTORY_SEPARATOR . "classes" . PATH_SEPARATOR
	. get_include_path());
require_once "Xinc.php";
require_once "Xinc/Api/Response/Object.php";
require_once "Xinc/Plugin/Repos/Api/Format/Json.php";
require_once 'Xinc/BaseTest.php';

class Xinc_Plugin_Repos_Api_Format_TestJson extends Xinc_BaseTest
{
	public function setUp()
	{
		parent::setUp();
		$this->json = new Xinc_Plugin_Repos_Api_Format_Json();
	}
	private function generateMockResponseObject($returnValue)
	{
		$responseMock = $this->getMock('Xinc_Api_Response_Object', array('get'));
		
		$responseMock->expects($this->once())
			->method('get')
			->will($this->returnValue($returnValue));
			
		return $responseMock;
	}
	/* see Issue 183 */
	public function testScalarValuesAreEnclosedInDoubleQuotes()
	{
		$responseMock = $this->generateMockResponseObject('scalarvalue');
		
		$this->assertEquals('"scalarvalue"', $this->json->generate($responseMock));
	}
	
	public function testListIsEncodedAsList()
	{		
		$responseMock = $this->generateMockResponseObject(array('string',0,false));
		
		$this->assertEquals("[\"string\",0,false]", $this->json->generate($responseMock));
	}
	
	public function testHashIsEncodedAsObject()
	{
		$responseMock = $this->generateMockResponseObject(array('property' => 'value'));
		
		$this->assertEquals("{\"property\":\"value\"}", $this->json->generate($responseMock));
	}
	
	public function testFormatPluginNameIsJson()
	{
		$this->AssertEquals('json', $this->json->getName());
	}
}