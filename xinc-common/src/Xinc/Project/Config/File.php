<?php
/**
 * Xinc Configuration File in XML Format
 * 
 * Reads a Xinc configuration file, parses it for validity
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

require_once 'Xinc/Project/Config/Exception/FileNotFound.php';
require_once 'Xinc/Project/Config/Exception/InvalidEntry.php';

class Xinc_Project_Config_File extends SimpleXMLElement
{
    
    private static $_allowedElements = array ( 'xinc',
                                               'xinc/project',
                                               
                                       );
    
    /**
     * Constructs a SimpleXMLElement
     *
     * @param string $fileName
     * @throws Xinc_Config_Exception_FileNotFound
     */
    public static function load($fileName)
    {
       
        if (!file_exists($fileName)) {
            throw new Xinc_Project_Config_Exception_FileNotFound($fileName);
        } else {
            $data = file_get_contents($fileName);
        }
        $file = new Xinc_Project_Config_File($data);
        
        $file->_validate();
        
        return $file;
    }
    
    /**
     * @throws Xinc_Project_Config_Exception_InvalidEntry
     */
    protected function _validate()
    {
        $array = array('xinc');
        foreach ($this->children() as $elementName => $element) {
            $parent = 'xinc/' . $elementName;
            $array[] = $parent;
            
        }
      
        foreach ( $array as $path ) {
            if (!in_array($path, self::$_allowedElements)) {
                throw new Xinc_Project_Config_Exception_InvalidEntry($path);
            }
        }
    }
    

   
    
}