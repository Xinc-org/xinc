<?php
/**
 * Test Class for the Xinc Project Registry
 * 
 * @package Xinc.Config
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
require_once 'Xinc/Config/File.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Config_TestParser extends Xinc_BaseTest
{
    
   
    public function testEmpty()
    {
        $workingdir = getcwd();
       
        $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testEmptySystem.xml');
       
        
        $parser = new Xinc_Config_Parser($configFile);
        
        $settings = $parser->getConfigSettings();
        $this->assertTrue( $settings->count() == 0 , 'No settings should be detected');
        
        $plugins = $parser->getPlugins();
        $this->assertTrue( $plugins->count() == 0 , 'No plugins should be detected');
        
        $engines = $parser->getEngines();
        $this->assertTrue( $engines->count() == 0 , 'No engines should be detected');
    }
    public function testNonEmpty()
    {
        $workingdir = getcwd();
       
        $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testNonEmptySystem.xml');
       
        
        $parser = new Xinc_Config_Parser($configFile);
        
        $settings = $parser->getConfigSettings();
        
        $this->assertTrue( $settings->count() > 0 , 'Settings should be detected');
        
        $plugins = $parser->getPlugins();
        $this->assertTrue( $plugins->count() > 0 , 'Plugins should be detected');
        
        $engines = $parser->getEngines();
        $this->assertTrue( $engines->count() > 0 , 'Engines should be detected');
        
    }

   
   
}