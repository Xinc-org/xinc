<?php
/**
 * Parses an array of SimpleXMLElements and generates Projects out of it
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
require_once 'Xinc/Project/Config/Iterator.php';

class Xinc_Project_Config_Parser
{
    /**
     *
     * @var Xinc_Project_Config_File
     */
    private $_configFile;
    
    public function __construct(Xinc_Project_Config_File $configFile)
    {
        $this->_configFile = $configFile;
    }
    
    /**
     * generates an array of all configured projects
     *
     * @return Xinc_Project_Iterator
     */
    public function getProjects()
    {
        $projects = $this->_configFile->xpath("//project");
        return new Xinc_Project_Config_Iterator($projects);
    }
    
    /**
     * Returns the name of the engine that has to be used for these Projects
     * @return mixed String or null if not found
     */
    public function getEngineName()
    {
        $xincAttributes = $this->_configFile->attributes();
        foreach ($xincAttributes as $name => $value) {
            if ($name == 'engine') return (string)$value;
        }
        return null;
    }
}