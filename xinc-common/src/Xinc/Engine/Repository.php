<?php
/**
 * Xinc - Continuous Integration.
 * Repository to manage all registered Engines
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Engine
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
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Engine/Iterator.php';
require_once 'Xinc/Engine/Exception/NotFound.php';

class Xinc_Engine_Repository
{

    private static $_instance;
    
    private $_defaultEngine;

    private $_engines = array();

    /**
     * Get an instance of the Plugin Repository
     *
     * @return Xinc_Engine_Repository
     */
    public static function getInstance()
    {
        if (!Xinc_Engine_Repository::$_instance) {
            Xinc_Engine_Repository::$_instance = new Xinc_Engine_Repository();
        }
        return Xinc_Engine_Repository::$_instance;
    }

    /**
     * Register a engine with the repository so that
     * builds can use it
     *
     * @param Xinc_Engine_Interface $engine
     * @param boolean $default
     *
     * @return boolean
     */
    public function registerEngine(Xinc_Engine_Interface &$engine, $default = false)
    {
        $engineClass = get_class($engine);
        
        if (!$engine->validate()) {
            Xinc_Logger::getInstance()->error('cannot load engine '
                                             . $engineClass);
                                             
            return false;
        }
       
        if (isset($this->_engines[$engine->getName()]) || isset($this->_engines[$engineClass])) {
            Xinc_Logger::getInstance()->error('cannot load engine '
                                             . $engineClass
                                             . ' already registered');
                                             
            return false;
        }
        $this->_engines[$engine->getName()] = $engine;
        $this->_engines[$engineClass] = $engine;
        
        if ($default) {
            $this->_defaultEngine = $engine;
        }
        
        return true;
    }
    
    /**
     * Returns Plugin Iterator
     *
     * @return Xinc_Iterator
     */
    public function getEngines()
    {
        return new Xinc_Engine_Iterator($this->_engines);
    }
    
    /**
     * returns the specified engine
     *
     * @param string $name
     *
     * @return Xinc_Engine_Interface
     * @throws Xinc_Engine_Exception_NotFound
     */
    public function getEngine($name)
    {
        if (empty($name) && isset($this->_defaultEngine)) {
            return $this->_defaultEngine;
        }
        if (isset($this->_engines[$name])) {
            return $this->_engines[$name];
        } else {
            throw new Xinc_Engine_Exception_NotFound($name);
        }
    }

    /**
     * remove reference of instance
     *
     */
    public static function tearDown()
    {
        self::$_instance = null;
    }
}