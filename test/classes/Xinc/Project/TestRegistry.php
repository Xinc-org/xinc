<?php
/**
 * Test Class for the Xinc Project Registry
 * 
 * @package Xinc
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
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
require_once 'Xinc/Project/Registry.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Registry/Exception.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Project_TestRegistry extends Xinc_BaseTest
{
    public function setUp()
    {
        $this->sharedFixture = Xinc_Project_Registry::getInstance();
    }
    
    public function testRegisterProject()
    {
        
        $project = new Xinc_Project();
        $noProject = new stdClass();
        try {
            $this->sharedFixture->register("test",$project);
            $this->assertTrue(true, 'No exception thrown');
        } catch (Xinc_Registry_Exception $re) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        }
        
        try {
            $this->sharedFixture->register("test",$project);
            $this->assertFalse(true, 'Expected exception');
        } catch (Xinc_Registry_Exception $re) {
            $this->assertTrue(true, 'Expected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        }
        
        try {
            $this->sharedFixture->register("test",$noProject);
            $this->assertFalse(true, 'No exception thrown');
        } catch (Xinc_Registry_Exception $re) {
            $this->assertTrue(true,  'Expected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        }
    }
    
    public function testUnregisterProject()
    {
        $projectName = md5(time());
        
        $project = new Xinc_Project();
        $project->setName($projectName);
        
        try {
            $this->sharedFixture->register($projectName,$project);
            $this->assertTrue(true, 'No exception thrown');
            $gotBackProject = $this->sharedFixture->unregister($projectName);
            
            $this->assertEquals($gotBackProject, $project, 'Projects should be equal');
            
        } catch (Xinc_Registry_Exception $re) {
            $this->assertFalse(true, 'Unexpected registry exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        }
        
        try {
            $this->sharedFixture->register($projectName,$project);
            $this->assertTrue(true, 'No exception thrown');
            $gotBackProject = $this->sharedFixture->unregister($projectName);
            
            $this->assertEquals($gotBackProject, $project, 'Projects should be equal');
            
        } catch (Xinc_Registry_Exception $re) {
            $this->assertFalse(true, 'Unexpected registry exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        }
        try {
            
            
            $gotBackProject = $this->sharedFixture->unregister($projectName);
            
            $this->assertFalse(true, 'Exception expected because project'
                                   . ' was unregistered and does'
                                   . ' not exist anymore');
            
        } catch (Xinc_Registry_Exception $re) {
            $this->assertTrue(true, 'Expected registry exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $e->getMessage());
        }
        try {
            
            
            $gotBackProject = $this->sharedFixture->get($projectName);
            
            $this->assertFalse(true, 'Exception expected because project'
                                   . ' was unregistered and does'
                                   . ' not exist anymore');
            
        } catch (Xinc_Registry_Exception $re) {
            $this->assertTrue(true, 'Expected registry exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $e->getMessage());
        }
    }
    public function testGet()
    {
        $projectName = md5(time());
        $notAProjectName = 'test';
        $project = new Xinc_Project();
        $project->setName($projectName);
        
        try {
            $this->sharedFixture->register($projectName,$project);
            $this->assertTrue(true, 'No exception thrown');
            $gotBackProject = $this->sharedFixture->get($projectName);
            
            $this->assertEquals($gotBackProject, $project, 'Projects should be equal');
            
        } catch (Xinc_Registry_Exception $re) {
            $this->assertFalse(true, 'Unexpected registry exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        }
        try {
            $this->sharedFixture->register($projectName,$project);
            $this->assertTrue(true, 'No exception thrown');
            $gotBackProject = $this->sharedFixture->get($notAProjectName);
            
            $this->assertFalse(true, 'No exception thrown');
            
        } catch (Xinc_Registry_Exception $re) {
            $this->assertTrue(true, 'Expected registry exception while '
                                   . 'registering a Xinc_Project:'
                                   . $re->getMessage());
        } catch (Exception $e) {
            $this->assertFalse(true, 'Unexpected exception while '
                                   . 'registering a Xinc_Project:'
                                   . $e->getMessage());
        }
    }
}