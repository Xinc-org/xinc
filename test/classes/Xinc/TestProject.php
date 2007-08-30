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
require_once 'Xinc/Project.php';
require_once 'Xinc/Builder/Fake.php';

require_once 'Xinc/Publisher/Fake.php';

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Xinc_TestProject extends  PHPUnit_Framework_TestCase
{
	private $project;

	public function setUp() 
	{
		$this->project = new Xinc_Project();
		$this->project->setName("project1");
		$this->project->setInterval(10);		
	}

	/**
	 * Tests to make sure the publisher is correctly triggered..
	 * 
	 */
	public function testPublisherPassesCorrectBuildResult() 
	{
		// setup a fake publisher (publishes on true build)
		$fakePublisher = new Xinc_Publisher_Fake();

		// always returns success for build
		$fakeBuilderSuccess = new Xinc_Builder_Fake(true);

		$this->project->addPublisher($fakePublisher);
		$this->project->setBuilder($fakeBuilderSuccess);

		// 
		$this->assertFalse($fakePublisher->getHasPublished(),
		'confirming that the fake publisher has not published yet'
		);
		
		// the publisher will publish only if the builder returns true..
		$this->project->build();
		$this->project->publish();

		// check that publisher has been called..		
		$this->assertTrue($fakePublisher->getHasPublished(),
		'confirming that the fake publisher has published now..'
		);
	}
	
	
	public function testPublisherPassesCorrectBuildResultFailure() 
	{
		// setup a fake publisher (publishes on true build)
		$fakePublisher = new Xinc_Publisher_Fake();

		// always returns fail for build
		$fakeBuilderFail = new Xinc_Builder_Fake(false);

		$this->project->addPublisher($fakePublisher);
		$this->project->setBuilder($fakeBuilderFail);

		// 
		$this->assertFalse($fakePublisher->getHasPublished(),
		'confirming that the fake publisher has not published yet'
		);
		
		// the publisher will publish only if the builder returns true..
		$this->project->build();
		$this->project->publish();

		// check that publisher has been called..		
		$this->assertFalse($fakePublisher->getHasPublished(),
		'confirming that the fake publisher has *NOT* published now..'
		);
	}
	
	function testSerialize() 
	{
		//$this->project->setLastBuildTime(time());
			
		//$this->project->serialize();
		
		$this->markTestIncomplete(
			'This test has not been completed yet.'
		);
	}
		
	function testCheckProjectsCheckExpired()
	{	// @todo how do we test this?
		
		// check  the project expires the timer
		
		
		// check the projects modification sets 
			// (fake one that is always true and always false)
		
		// check above with multiple mod sets
		
		// if modification is detected test buildPassed and !buildPassed  ..(force a fail)..
		
		$this->markTestIncomplete(
			'This test has not been completed yet.'
		);
	}
}