<?php
/**
 * Xinc - Continuous Integration.
 * The Api Repository holds all the Api Modules
 * that are defined by the loaded plugins.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Api
 * @author    Arno Schneider <username@example.org>
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

/**
 * The Widget-Repository allows the Web-Frontend of Xinc to choose the right
 * plugin for execution based on the Http-Request
 *
 * @package Xinc.Gui
 * @license http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 * @author  Arno Schneider
 */
class Xinc_Api_Module_Repository
{
    /**
     * @var Xinc_Gui_Widget_Repository
     */
    private static $_instance;

    /**
     * @var Xinc_Api_Module_Interface[]
     */
    private $_definedModules = array();
    
    /**
     * Contains all the registered api modules
     *
     * @var Xinc_Api_Module_Interface[]
     */
    private $_modules = array();
    
    /**
     * Return an instance of the Widget Repository
     *
     * @return Xinc_Api_Module_Repository
     */
    public static function getInstance()
    {
        if (!Xinc_Api_Module_Repository::$_instance) {
            Xinc_Api_Module_Repository::$_instance = new Xinc_Api_Module_Repository();
        }
        return Xinc_Api_Module_Repository::$_instance;
    }
    
    /**
     * Register a module with the Repository
     *
     * @param Xinc_Api_Module_Repository $module
     */
    public function registerModule(Xinc_Api_Module_Interface $module)
    {
        $moduleName = $module->getName();
        $this->_definedModules[$moduleName] = array();
        /**
         *  Determine the Methods which
         * the Api will be called for
         */
        $methods = $module->getMethods();
        foreach ($methods as $method) {
            /**
             * register the method for the module
             */
            $this->_definedModules[$moduleName][] = $method;
        }
        $this->_modules[$moduleName] = $module;
        
    }

    /**
     * Determines the Api Module that should be used
     * for the specified Name that 
     * is called
     *
     * @param String $moduleName Name of the module
     *
     * @return Xinc_Api_Module_Interface
     */
    public function &getModuleByName($moduleName)
    {
        
        $module = null;
        if (isset($this->_modules[$moduleName])) {
            $module = $this->_modules[$moduleName];
        }
        return $module;
    }
    
    /**
     * Determines the Api Module that should be used
     * for the specified Name that 
     * is called
     *
     * @param String $moduleName Name of the module
     * @param String $methodName Name of the method
     *
     * @return Xinc_Api_Module_Interface
     */
    public function &getModuleByNameAndMethod($moduleName, $methodName)
    {
        $module = null;
        if (isset($this->_definedModules[$moduleName])) {
            $methods = $this->_definedModules[$moduleName];
            if (in_array($methodName, $methods)) {
                /**
                 * The method exists
                 */
                $module = $this->_modules[$moduleName];
            }
        }
        return $module;
    }
    
    /**
     * Returns all the registered Widgets
     *
     * @return Xinc_Api_Module_Interface[]
     */
    public function getModules()
    {
        return $this->_modules;
    }
}