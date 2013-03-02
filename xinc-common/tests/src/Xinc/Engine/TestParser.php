<?php
/**
 * Test Class for the Xinc Engine Parser
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
require_once 'Xinc/Engine/Parser.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Engine_TestParser extends Xinc_BaseTest
{
   
   
    public function testFileNotFound()
    {
        
        /**
         * reset the engines
         */
        Xinc_Engine_Repository::tearDown();
        
        $workingdir = getcwd();
        $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testEngineFileNotFound.xml');
        
        $parser = new Xinc_Config_Parser($configFile);
        
        $engines = $parser->getEngines();
        
        $this->assertTrue( count($engines) == 1, 'We should have one engine');
        
        $engineParser = new Xinc_Engine_Parser();
        
        try {
            $engineParser->parse($engines);
            $engine = Xinc_Engine_Repository::getInstance()->getEngine('Xinc_Engine_Sunrise');
            $this->assertFalse(true, 'Should throw an exception');
        } catch (Xinc_Engine_Exception_NotFound $e1) {
            $this->assertTrue(true, 'Right exception caught');
        } catch (Exception $e2) {
            
            $this->assertTrue(false, 'Should catch a FileNotFOund exception but caught: ' . get_class($e));
        }
    }
    
    public function testClassNotFound()
    {
        
        /**
         * reset the engines
         */
        Xinc_Engine_Repository::tearDown();
        
        $workingdir = getcwd();
       
        
        $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testEngineClassNotFound.xml');
        
        
        $parser = new Xinc_Config_Parser($configFile);
        
        $engines = $parser->getEngines();
       
        $this->assertTrue( $engines->count() == 1, 'We should have one engine');
        
        $engineParser = new Xinc_Engine_Parser();
        
        try {
            $engineParser->parse($engines);
            $engine = Xinc_Engine_Repository::getInstance()->getEngine('Xinc_Engine_Sunrise');
            $this->assertFalse(true, 'Should throw an exception');
        } catch (Xinc_Engine_Exception_NotFound $e) {
            
            $this->assertTrue(true, 'Right exception caught');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should catch a ClassNotFOund exception but caught: ' . get_class($e));
        }
        
        $engines = Xinc_Engine_Repository::getInstance()->getEngines();
        
        $this->assertEquals($engines->count(), 0, 'There should not be a registered engine');
        
        $engines = Xinc_Engine_Repository::getInstance()->getEngines();
        
        $this->assertEquals($engines->count(), 0, 'There should not be a registered engine');
    }
    public function testInvalidEngine()
    {
        
        /**
         * reset the engines
         */
        Xinc_Engine_Repository::tearDown();
        
        $workingdir = getcwd();
       

        $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testEngineInvalid.xml');

        
        $parser = new Xinc_Config_Parser($configFile);
        
        $engines = $parser->getEngines();
        
        $this->assertTrue( count($engines) == 1, 'We should have one engine');
        
        $engineParser = new Xinc_Engine_Parser();
        
        try {
            $engineParser->parse($engines);
            $engine = Xinc_Engine_Repository::getInstance()->getEngine('Xinc_Engine_Sunrise');
            //$this->assertFalse(true, 'Should throw an exception');
        } catch (Xinc_Engine_Exception_NotFound $e) {
            $this->assertTrue(true, 'Right exception caught');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should catch a Invalid exception but caught: ' . get_class($e));
        }
        
        $engines = Xinc_Engine_Repository::getInstance()->getEngines();
        
        $this->assertEquals($engines->count(), 0, 'There should not be a registered engine');
        
        $engines = Xinc_Engine_Repository::getInstance()->getEngines();
        
        $this->assertEquals($engines->count(), 0, 'There should not be a registered engine');
    }    
    public function testEngineRegistered()
    {
        
        /**
         * reset the engines
         */
        Xinc_Engine_Repository::tearDown();
        
        $workingdir = getcwd();
       
        $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testSystem.xml');
       
        
        $parser = new Xinc_Config_Parser($configFile);
        
        $engines = $parser->getEngines();
        
        $this->assertTrue( count($engines) == 1, 'We should have one engine');
        
        $engineParser = new Xinc_Engine_Parser();
        
        try {
            $engineParser->parse($engines);
            $this->assertTrue(true, 'Should not throw an exception');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should not catch an exception but caught: ' . get_class($e));
        }
        
        $engines = Xinc_Engine_Repository::getInstance()->getEngines();
        
        /**
         * Engines are registered by their name and classname
         * therefore we expect 2 engines
         */
        $this->assertEquals($engines->count(), 2, 'There should be a registered engine');
        
        $engine = Xinc_Engine_Repository::getInstance()->getEngine(Xinc_Engine_Sunrise::NAME);
        
        $this->assertTrue($engine instanceof Xinc_Engine_Sunrise, 'Engine should be a Xinc_engine_Sunrise');
    }
}