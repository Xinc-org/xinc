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

class Xinc_Project_TestConfig extends Xinc_BaseTest
{
    
   
    public function testValidConfig()
    {
        $workingdir = getcwd();
       
        try {
            $config = new Xinc_Project_Config($workingdir .'/test/resources/testNonEmptyProjects.xml');
           
            
            $projects = $config->getProjects();
            
            $this->assertTrue($projects instanceof Xinc_Project_Iterator, 'Should be of instance project iterator ' 
                                                                        . ' but is:' . get_class($projects));
            $engineName = $config->getEngineName();
            
            $this->assertTrue($engineName != null, 'Should have an engine name');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should not throw an exception but did: ' . get_class($e));
        }
    }
   

   
   
}