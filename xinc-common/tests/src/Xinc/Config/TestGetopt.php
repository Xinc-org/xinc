<?php
/**
 * Test Class for the Xinc Getopt File
 *
 * @package Xinc.Config
 * @author Jamie Talbot
 * @version 2.0
 * @copyright 2007 Jamie Talbot, England
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

require_once 'Xinc.php';
require_once 'Xinc/Config/Getopt.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Config_TestGetopt extends Xinc_BaseTest
{
	private $_arguments;
	private $_workingDir;

	public function setUp() {
    $this->_workingdir = getcwd();

		$this->_arguments = array(
			'validOptions' => '-f demo.xml',
			'missingParameter' => '-f',
			'allowOptionsAfterProjectFiles' => '-f demo.xml simpleproject.xml -o',
			'allowMultipleProjectFilesInAnyOrder' => '-f demo.xml simpleproject.xml -o simplerproject.xml '
		);
	}

	public function testCanParseValidOptions()
	{
		try {
			$args = explode(' ', $this->_arguments['validOptions']);
			$arguments = Xinc_Config_Getopt::getopt($args, Xinc::getShortOptions(), Xinc::getLongOptions());
			$this->assertEquals('f', $arguments[0][0][0], 'Config file should be demo.xml');
			$this->assertEquals('demo.xml', $arguments[0][0][1], 'Config file should be demo.xml');
		} catch (Exception $e) {
			$this->assertTrue(false, 'Should not catch any exceptions');
		}
	}

	public function testCanParseMultipleProjectFilesInAnyOrder()
	{
		try {
			$args = explode(' ', $this->_arguments['allowMultipleProjectFilesInAnyOrder']);
			list($options, $nonOptions) = Xinc_Config_Getopt::getopt($args, Xinc::getShortOptions(), Xinc::getLongOptions());
			$this->assertEquals('f', $options[0][0], 'Config file should be demo.xml');
			$this->assertEquals('demo.xml', $options[0][1], 'Config file should be demo.xml');
			$this->assertEquals('o', $options[1][0], 'We should be running once.');
			$this->assertEquals(null, $options[1][1], "There shouldn't be an argument for run once.");
			$this->assertEquals('simpleproject.xml', $nonOptions[0], 'We should have two project files.');
			$this->assertEquals('simplerproject.xml', $nonOptions[1], 'We should have two project files.');
		} catch (Exception $e) {
			$this->assertTrue(false, 'Should not catch any exceptions');
		}
	}

	public function	testCanParseOptionsAfterProjectFiles()
	{
		try {
			$args = explode(' ', $this->_arguments['allowOptionsAfterProjectFiles']);
			list($options, $nonOptions) = Xinc_Config_Getopt::getopt($args, Xinc::getShortOptions(), Xinc::getLongOptions());
			$this->assertEquals('f', $options[0][0], 'Config file should be demo.xml');
			$this->assertEquals('demo.xml', $options[0][1], 'Config file should be demo.xml');
			$this->assertEquals('o', $options[1][0], 'We should be running once.');
			$this->assertEquals(null, $options[1][1], "There shouldn't be an argument for run once.");
			$this->assertEquals('simpleproject.xml', $nonOptions[0], 'We should have a single project file.');
		} catch (Exception $e) {
			$this->assertTrue(false, 'Should not catch any exceptions');
		}
	}

	public function testWillFailOnMissingConfigFile()
	{
		try {
			$args = explode(' ', $this->_arguments['missingParameter']);
			$arguments = Xinc_Config_Getopt::getopt($args, Xinc::getShortOptions(), Xinc::getLongOptions());
		} catch (Exception $e) {
			return true;
		}
		$this->assertTrue(false, 'Should have thrown a missing config file exception');
	}

	public function tearDown() {
		unset($this->_arguments);
		unset($this->_workingDir);
	}
}