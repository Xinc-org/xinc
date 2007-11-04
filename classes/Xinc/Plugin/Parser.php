<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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
require_once 'Xinc/Plugin/Repository.php';

class Xinc_Plugin_Parser
{
    
    private $_plugins=array();
    /**
     * Public parse function
     *
     * @throws Xinc_Exception_MalformedConfig
     */
    public function parse($configFile)
    {
        try {
            return $this->_parse($configFile);
        }
        catch(Exception $e) {
            //var_dump($e);
            throw $e;
        }
    }



    private function _parse($configFile)
    {

        $repository = new Xinc_Plugin_Repository();
        $xml = new SimpleXMLElement(file_get_contents($configFile));


        $plugins=array();
        foreach ($xml->plugin as $pluginXml) {
            $attributes=$pluginXml->attributes();
            
            try{
            
                require_once($attributes->filename);
                $classname=(string)$attributes->classname;
                $plugin=new $classname;
                Xinc_Plugin_Repository::getInstance()->registerPlugin($plugin);
            }
            catch(Xinc_Plugin_Task_Exception $e){
                return false;
            }
                
        }
        return true;
    }

    
}