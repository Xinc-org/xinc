<?php
/**
 * Test Class for the Xinc Build Properties
 * 
 * @package Xinc.Build
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
require_once 'Xinc/Build/Properties.php';

require_once 'Xinc/BaseTest.php';

class Xinc_Build_TestProperties extends Xinc_BaseTest
{
    
   
    public function testProperties()
    {
        $properties = new Xinc_Build_Properties();
        
        $value = time();
        $properties->set('test', $value);
        
        $this->assertEquals($properties->get('test'), $value, 'Values should be equal');
        
        $this->assertEquals($properties->get('NonExistant'), null, 'Value should not exist');

        
        $stringToParse = 'test: ${test} :test';
        $expectedString = 'test: ' . $value . ' :test';
        
        $stringParsed = $properties->parseString($stringToParse);
        
        $this->assertTrue($stringToParse != $stringParsed, 'Should not be the same anymore');
        $this->assertTrue($stringParsed == $expectedString, 'Should match "' 
                                                        . $expectedString . '" but is "'
                                                        . $stringParsed . '"');
    }
    
    public function testGetAllProperties()
    {
        $propertiesArr = array();
        for ($i=0; $i< 100; $i++) {
            $propertiesArr[$i] = $i;
        }
        
        $properties = new Xinc_Build_Properties();
        foreach ($propertiesArr as $key => $value) {
            $properties->set($key, $value);
        }
        
        $allProperties = $properties->getAllProperties();
        
        $this->assertEquals($allProperties, $propertiesArr, 'Arrays should match');
    }

   
   
}