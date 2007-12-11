<?php
/**
 * Api to get information about builds
 * 
 * @package Xinc.Plugin
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
require_once 'Xinc/Api/Module/Interface.php';
require_once 'Xinc/Plugin/Repos/Api/Format/Json.php';
require_once 'Xinc/Api/Handler.php';

class Xinc_Plugin_Repos_Api_Formats implements Xinc_Api_Module_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Plugin_Interface
     */
    protected $_plugin;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
        Xinc_Api_Handler::getInstance()->registerResponseFormat(new Xinc_Plugin_Repos_Api_Format_Json());
        
    }
    public function getName()
    {
        return '_register_formats_';
    }
    public function getMethods()
    {
        return array();
    }
    public function processCall($methodName, $params = array())
    {

       
    }
    
    
}