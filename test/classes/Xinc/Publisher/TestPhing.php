<?php
/**
 * Phing Publisher
 * 
 * @package Xinc.Publisher
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
 
require_once 'Xinc/Publisher/Phing.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
 
class Xinc_Publisher_TestPhing extends  PHPUnit_Framework_TestCase {
 	
	private $phingPublisherOnSuccess;
	private $phingPublisherOnFailure;

	private $name;
	
	function setUp() 
	{
		$this->phingPublisherOnSuccess = new Xinc_Publisher_Phing();
		$this->phingPublisherOnFailure = new Xinc_Publisher_Phing();
	
		$this->phingPublisherOnSuccess->setPublishOnSuccess(true);
		$this->phingPublisherOnFailure->setPublishOnFailure(true);

	}
	
	function tearDown() 
	{
	}
	
	function testPhingPublishOn() {
		$this->assertTrue($this->phingPublisherOnSuccess->publishOn(true),'checking that an on success publisher is working');
		$this->assertFalse($this->phingPublisherOnFailure->publishOn(true),'checking that an on failure publisher is working');

		$this->assertFalse($this->phingPublisherOnSuccess->publishOn(false),'checking that an on success publisher is working');
		$this->assertTrue($this->phingPublisherOnFailure->publishOn(false),'checking that an on failure publisher is working');
	} 	
 
 	function testPhingPublish() {
 		// I think that testing the PhingBuilder is good enough, this is just a wrapper..
 	}
}