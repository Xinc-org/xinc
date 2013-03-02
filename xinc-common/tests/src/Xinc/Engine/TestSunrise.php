<?php
/**
 * Test Class for the Xinc Engine Sunrise 
 *
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
require_once 'Xinc/Engine/Sunrise.php';
require_once 'Xinc/Plugin/Repos/Builder.php';
require_once 'Xinc/Plugin/Repos/ModificationSet.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/BuildAlways.php';
require_once 'Xinc/Plugin/Repos/Phing.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Engine_TestSunrise extends Xinc_BaseTest
{
   
   
    public function testSettersGetters()
    {
        $engine = new Xinc_Engine_Sunrise();
        $name = 'Sunrise';
        
        $this->assertEquals($engine->getName(), $name, 'Name has to be Sunrise');
        
        $heartBeat = rand(10,100);
        $engine->setHeartBeat($heartBeat);
        
        $this->assertEquals($heartBeat, $engine->getHeartBeat(), 'Heartbeat does not match');
    }
    
    public function testExample()
    {
        
        Xinc_Plugin_Repository::tearDown();
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_ModificationSet());
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Builder());
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_ModificationSet_BuildAlways());
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Phing());

        $workingdir = getcwd();
        $engine = new Xinc_Engine_Sunrise();
        $config = new Xinc_Project_Config($workingdir .'/test/resources/testSunriseExampleProject.xml');
       
        $buildIterator = $engine->parseProjects($config->getProjects());
        
        $this->assertTrue($buildIterator instanceof Xinc_Build_Iterator, 'Should be of type Xinc_Build_Iterator');

        $build = $buildIterator->next();
        try {

        
        $build->build();
        } catch (Exception $e) {
            var_dump($e);
        }
        $this->assertTrue($build->getLastBuild()->getStatus() == Xinc_Build_Interface::PASSED, 'Build should pass');
        
       
    }

   
}