<?php
/**
 * Test Class for the Xinc Plugin "Property"
 * 
 * @package Xinc.Plugin
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
require_once 'Xinc.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Engine/Sunrise.php';
require_once 'Xinc/Plugin/Repos/Property.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Plugin_Repos_TestProperty extends Xinc_BaseTest
{
    
   
    public function testBuildProperties()
    {
        Xinc_Plugin_Repository::tearDown();
        Xinc_Plugin_Repository::getInstance()->registerPlugin(new Xinc_Plugin_Repos_Property());
        
        
        $workingdir = getcwd();
        $engine = new Xinc_Engine_Sunrise();
        $config = new Xinc_Project_Config($workingdir .'/test/resources/testProjectsPlugProperty.xml');
       
        $buildIterator = $engine->parseProjects($config->getProjects());
    
        
        $this->assertTrue($buildIterator instanceof Xinc_Build_Iterator, 'Should be of type Xinc_Build_Iterator');
        

        $build = $buildIterator->next();
        $engine->build($build);
        $original = $build->getProperties()->get('original');
        $compare = $build->getProperties()->get('compare');
        
        $this->assertEquals($original, $compare, 'Value should have been substituted and should be equal now: ' .
                                                 $original . '!=' .
                                                 $compare);
    }   
   
}