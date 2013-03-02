<?php
/**
 * Test Class for the Xinc Engine Sunrise Parser
 * 
 * @package Xinc.Engine
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
require_once 'Xinc/BaseTest.php';

require_once 'Xinc/Config/File.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/Engine/Sunrise/Parser.php';
require_once 'Xinc/Plugin/Repos/ModificationSet.php';

class Xinc_Engine_Sunrise_TestParser extends Xinc_BaseTest
{
    
   
    public function testExample()
    {
        $workingdir = getcwd();
        try {
            Xinc_Plugin_Repository::getInstance()->tearDown();
            Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_ModificationSet());
            Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Builder());
            Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_ModificationSet_BuildAlways());
        
            Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Phing());
        } catch (Exception $e) {
            var_dump($e);
        }
        $configFileName = $workingdir .'/test/resources/testSunriseExampleProject.xml';
       
        
        $config = new Xinc_Project_Config($configFileName);
        
        
        
        $projects = $config->getProjects();
       
      
        $engineParser = new Xinc_Engine_Sunrise_Parser(new Xinc_Engine_Sunrise());
       
        $result = $engineParser->parseProjects($projects);
        
        /**
         * Expecting one Xinc_Build
         */
        $this->assertTrue(count($result) == 1, 'Should have one item in the array');
        $this->assertTrue($result[0] instanceof Xinc_Build_Interface, 
                          'Object has to implement the Xinc_Build_Interface');
        
    }

    public function testExampleInvalid()
    {
        $workingdir = getcwd();
        Xinc_Plugin_Repository::tearDown();
        
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_ModificationSet());
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Builder());
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_ModificationSet_BuildAlways());
        
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Phing());
        
        $configFileName = $workingdir .'/test/resources/testSunriseExampleInvalidProject.xml';
       
        
        $config = new Xinc_Project_Config($configFileName);
        
        
        
        $projects = $config->getProjects();
       
      
        $engineParser = new Xinc_Engine_Sunrise_Parser(new Xinc_Engine_Sunrise());
       
        
        $result = $engineParser->parseProjects($projects);
        
        $this->assertTrue(count($result) == 1, 'Should have one item in the array');
        
        $build1 = $result[0];
        $projectStatus = $build1->getProject()->getStatus();
        $this->assertEquals(Xinc_Project_Status::MISCONFIGURED, $projectStatus,
                            'Project status should be MISCONFIGURED but is: ' . $projectStatus);
       
        
    }
   
}