<?php
/**
 * Registry for all projects configured to run with Xinc
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
require_once 'Xinc/Registry/Abstract.php';

class Xinc_Project_Registry extends Xinc_Registry_Abstract
{
    private static $_instance;
    
    public static function &getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Xinc_Project_Registry();
        }
        
        return self::$_instance;
    }
    
    /**
     * Enter description here...
     *
     * @param string $name
     * @param object $object
     * @throws Xinc_Registry_Exception
     */
    public function register($name, &$object)
    {
        if (!$object instanceof Xinc_Project) {
            throw new Xinc_Registry_Exception('Object with name "'
                                             . $name 
                                             . '" is not a Xinc_Project');
            // @codeCoverageIgnoreStart
        }
            // @codeCoverageIgnoreEnd
        
        parent::register($name, $object);
    }
}