<?php
/**
 * Test class for the SVN Modification Set.
 * 
 * @package Xinc.ModificationSet
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
 
// @todo parameterize working copy version!  hardcoded to alchemy branch...
require_once 'Xinc/ModificationSet/Svn.php'; 
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
 
class Xinc_ModificationSet_TestSvn extends  PHPUnit_Framework_TestCase {
	private $svnModificationSet;
	private $name;

	/**
	 * 	 before this test runs we need a working copy!
	 */
	function setUp() 
	{
		$cwd = getcwd();
		@mkdir('testFiles');
		chdir("testFiles");
		@mkdir("notworkingcopy");
		$result = `svn co http://xinc.entrypoint.biz/svn/xinc/trunk xinc`;
		chdir($cwd);
	}

	/**
	 * when we shut down the test, we need to remove the working copy!
	 */
	function tearDown() 
	{
		$cwd = getcwd();
		chdir("testFiles");	
		@rmdir("notworkingcopy");
		$result = `rm -rf xinc`;
		chdir($cwd);		
		@rmdir('testFiles');
	}

	function testCheckModifiedUnchanged() 
	{
		$svnModificationSet = new Xinc_ModificationSet_Svn();
		$svnModificationSet->setDirectory("testFiles/xinc");
		$modifiedFlag = $svnModificationSet->checkModified();
		$this->assertFalse($modifiedFlag);
	}
	
	function testCheckModifiedNoCheckoutDirectory() 
	{
		try {	
	
			$svnModificationSet = new Xinc_ModificationSet_Svn();
			$svnModificationSet->setDirectory("testFiles/nothere");
			$modifiedFlag = $svnModificationSet->checkModified();
		
			// should never get here
			$this->assertFalse($modifiedFlag);
		}
		catch(Xinc_Exception_ModificationSet $e) {
			return;
		}

		$this->fail("Expected ModificationSet exception");
	}

	function testCheckModifiedNotWorkingCopy()
	{
		try {	
	
			$svnModificationSet = new Xinc_ModificationSet_Svn();
			$svnModificationSet->setDirectory("testFiles/notworkingcopy");
			$modifiedFlag = $svnModificationSet->checkModified();

			// should never get here
			$this->assertFalse($modifiedFlag);
		}
		catch(Xinc_Exception_ModificationSet $e) {
			return;
		}

		$this->fail("Expected ModificationSet exception");
	}

	/*
	 * @todo make sure it fails gracefully if the remote repos has a problemo.
	 */
	function testCheckModifiedRemoteReposError()
	{
/*
		try {	
	
			$svnModificationSet = new Xinc_ModificationSet_Svn();
			$svnModificationSet->setDirectory("testFiles/notworkingcopy");
			$modifiedFlag = $svnModificationSet->checkModified();

			 should never get here
			$this->assertFalse($modifiedFlag);
		}
		catch(Xinc_Exception_ModificationSet $e) {
			return;
		}

		$this->fail("Expected ModificationSet exception");
*/
	}


	// @todo how do we set up a test environment to detect mods?
	function testCheckModifiedChanged()
	{
		// ideas:  abstract out the revision code so we can replace with 
		// fake versions with known outcomes?
		
		// create a repository branch that self modifies when the test is run?
		// not so much..
		
		// not sure then, any ideas?
	}
	
	/**
	 * We need to correctly handle the case where the working copy
	 * is not present or somehow incorrect.
	 * 
	 */
	function testCheckInvalidWorkingCopy() 
	{
	}
	
}
?>
