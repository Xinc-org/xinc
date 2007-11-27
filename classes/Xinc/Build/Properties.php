<?php
/**
 * Build Properties carry additional information about a build
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
class Xinc_Build_Properties
{
    
    /**
     * Associative Array holding the nvp for the build properties
     *
     * @var array
     */
    private $_properties = array();
    
    /**
     * set a property
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->_properties[$name] = $value;
    }
    
    public function get($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            return null;
        }
    }
    
    public function getAllProperties()
    {
        return $this->_properties;
    }
    /**
     * Parses a string and substitutes ${name} with $value
     * of property
     *
     * @param string $string
     */
    public function parseString($string)
    {
        $string = (string) $string;
        $string = preg_replace("/\\$\{(.*?)\}/", '{$this->_properties[\'\\1\']}', $string);
        
        $evalString = '$newString = "'.$string.'";';
        
        
        @eval($evalString);
        
        return $newString;
    }
}