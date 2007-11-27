<?php
/**
 * Test Class for the Xinc Config
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
require_once 'Xinc/Config.php';

require_once 'Xinc/BaseTest.php';

class Xinc_TestConfig extends Xinc_BaseTest
{
    
   
    public function testInvalidConfig()
    {
        $workingdir = getcwd();
        try {
            $configFilename = $workingdir .'/test/resources/testSystemInvalid.xml';
            $config = Xinc_Config::parse($configFilename);
            $this->assertTrue(false, 'It is invalid, should throw an exception');
            
        } catch (Xinc_Config_Exception_InvalidEntry $invalidEntry) {
            $this->assertTrue(true, 'Correct exception thrown');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should have caught ' 
                                   . 'Xinc_Config_Exception_InvalidEntry '
                                   . 'but caught: ' . get_class($e));
                                 
        }
    }
    public function testValidConfig()
    {
        $workingdir = getcwd();
        try {
            $configFilename = $workingdir .'/test/resources/testSystem.xml';
            Xinc_Config::parse($configFilename);
            $this->assertTrue(true, 'Should not throw an exception');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should not throw an exception');
        }
    }

   
   
}