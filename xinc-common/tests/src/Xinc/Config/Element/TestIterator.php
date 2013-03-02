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
require_once 'Xinc/Config/Element/Iterator.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Config_Element_TestIterator extends Xinc_BaseTest
{
    public function testValid()
    {
        $arr = array();
        $element = new SimpleXMLElement("<string/>");
        $arr[] = $element;
        try {
            $iterator = new Xinc_Config_Element_Iterator($arr);
            
            $hasNext = $iterator->hasNext();
            
            $this->assertTrue($hasNext, 'Should be true');
            
            $count = $iterator->count();
            
            $this->assertEquals($count, 1, 'Should have 1 entry but has: ' . $count);
            
            $next = $iterator->next();
            
            $this->assertEquals($element, $next, 'Elements should be equal');
            
            $iterator->rewind();
            
             $hasNext = $iterator->hasNext();
            
            $this->assertTrue($hasNext, 'Should be true');
            
            $count = $iterator->count();
            
            $this->assertEquals($count, 1, 'Should have 1 entry but has: ' . $count);
            
            $next = $iterator->next();
            
        } catch (Xinc_Config_Exception_InvalidElement $invalid) {
            $this->assertTrue(false, 'Expected result');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Not expected');
        }
    }
    public function testInvalid()
    {
        $arr = array(1,2);
        
        try {
            $iterator = new Xinc_Config_Element_Iterator($arr);
        } catch (Xinc_Config_Exception_InvalidElement $invalid) {
            $this->assertTrue(true, 'Expected result');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Not expected');
        }
    }
  
    
   
}