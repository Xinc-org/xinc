<?php
/**
 * Test Class for the Xinc Project Config Parser
 * 
 * @package Xinc.Project
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
require_once 'Xinc/Project/Config/File.php';
require_once 'Xinc/Project/Config/Parser.php';

require_once 'Xinc/BaseTest.php';

class Xinc_Project_Config_TestParser extends Xinc_BaseTest
{
    
   
    public function testEmpty()
    {
        $workingdir = getcwd();
       
        $configFile = Xinc_Project_Config_File::load($workingdir .'/test/resources/testEmptyProjects.xml');
       
        
        $parser = new Xinc_Project_Config_Parser($configFile);
        
        
        
        $projects = $parser->getProjects();
        $this->assertTrue( $projects->count() == 0 , 'No projects should be detected');
      
       
        
    }

    public function testNonEmpty()
    {
        $workingdir = getcwd();
       
        $configFile = Xinc_Project_Config_File::load($workingdir .'/test/resources/testNonEmptyProjects.xml');
       
        
        $parser = new Xinc_Project_Config_Parser($configFile);
        
        $projects = $parser->getProjects();
        $this->assertTrue( $projects->count() == 1 , 'One project should be detected');
      
        $engineName = $parser->getEngineName();
        
        $this->assertTrue($engineName!=null, 'Should return an engine name');
        
    }
   
   
}