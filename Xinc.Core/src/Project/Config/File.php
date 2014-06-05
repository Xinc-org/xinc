<?php
/**
 * Xinc Configuration File in XML Format
 * 
 * Reads a Xinc configuration file, parses it for validity
 * 
 * @package   Xinc.Core
 * @author    Arno Schneider <username@example.com>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Core\Project\Config;

class File extends \SimpleXMLElement
{
    
    private static $allowedElements = array(
        'xinc',
        'xinc/project',
    );
    
    /**
     * Constructs a SimpleXMLElement
     *
     * @param string $fileName
     *
     * @throws Xinc\Core\Project\Config\Exception\FileNotFoundException
     */
    public static function load($fileName)
    {
       
        if (!file_exists($fileName)) {
            throw new Exception\FileNotFoundException($fileName);
        } else {
            $data = file_get_contents($fileName);
        }
        $file = new File($data);
        
        $file->validate();
        
        return $file;
    }
    
    /**
     * @throws Xinc\Core\Project\Config\Exception\InvalidEntryException
     */
    protected function validate()
    {
        $array = array('xinc');
        foreach ($this->children() as $elementName => $element) {
            $parent = 'xinc/' . $elementName;
            $array[] = $parent;
            
        }

        foreach ($array as $path) {
            if (!in_array($path, self::$allowedElements)) {
                throw new Exception\InvalidEntryException($path);
            }
        }
    }
}
