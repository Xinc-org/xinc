<?php
/**
 * Xinc - Continuous Integration.
 * This class handles the API activities of Xinc
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

require_once 'Xinc/Gui/Event.php';
require_once 'Xinc/Gui/Widget/Repository.php';
require_once 'Xinc/Api/Module/Repository.php';
require_once 'Xinc/Config.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Timezone.php';


class Xinc_Api_Handler
{
    private $_basePath;
    private static $_instance;
    private $_responseFormats = array();
    
    /**
     * @param string $basePath
     * @return Xinc_Api_Handler
     */
    public static function getInstance($basePath = '/api')
    {
        if (!isset(Xinc_Api_Handler::$_instance)) {
            Xinc_Api_Handler::$_instance = new Xinc_Api_Handler($basePath);
        }
        return Xinc_Api_Handler::$_instance;
    }
    
    /**
     * Constructor for the api handler, setting up the baseurl for the api calls
     *
     * @param string $basePath baseurl for the API calls
     */
    private function __construct($basePath = '/api')
    {
        $this->_basePath = $basePath;
    }
    
    /**
     * Register a response format with the api handler
     * - Calls to a api contain the request for a specific response forma
     *   like json etc.
     *
     * @param Xinc_Api_Response_Format_Interface $format
     */
    public function registerResponseFormat(Xinc_Api_Response_Format_Interface &$format)
    {
        $this->_responseFormats[$format->getName()] = $format;
    }
    
    /**
     * Processes the call to a specific api url
     *
     * @param String $path
     */
    public function processCall($path)
    {
        
        /**
         * Matching pattern /$this->_basePath/$modulename/$methodname/$format/
         */
        preg_match('/\\' . $this->_basePath . '\/(.*?)\/(.*?)\/(.*?)\/.*/', $path, $matches);
        if (count($matches)<4) {
            die('404');
        }

        $moduleName = $matches[1];
        $methodName = $matches[2];
        $formatName = $matches[3];
        
        if (!isset($this->_responseFormats[$formatName])) {
            die('UNKNOWN FORMAT: ' . $formatName);
        } else {
            $format = $this->_responseFormats[$formatName];
        }
        $module = Xinc_Api_Module_Repository::getInstance()->getModuleByNameAndMethod($moduleName, $methodName);
        if ($module != null) {
            $res = $module->processCall($methodName, $_REQUEST);
            echo $format->generate($res);
        }
    }
    
    /**
     * Returns the baseurl of the api handler
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }
}