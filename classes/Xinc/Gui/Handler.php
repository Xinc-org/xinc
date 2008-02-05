<?php
/**
 * This class handles the core Frontend-Activity of Xinc
 * 
 * @package Xinc.Gui
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

require_once 'Xinc/Gui/Event.php';
require_once 'Xinc/Gui/Widget/Repository.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/Config/File.php';
require_once 'Xinc/Plugin/Parser.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Api/Handler.php';
require_once 'Xinc/Timezone.php';

class Xinc_Gui_Handler
{
    
    /**
     * Plugin Parser: is used to load all the plugins and
     * the registered Widgets
     *
     * @var Xinc_Plugin_Parser
     */
    private $_pluginParser;
    
    /**
     * Directory of the project-status files generated
     * by the Xinc-Process
     *
     * @var string
     */
    private $_statusDir;
    
    /**
     * @var Xinc_Gui_Handler
     */
    private static $_instance;
    
    /**
     *
     * @var Xinc_Api_Handler
     */
    private $_apiHandler;
    
    private $_systemTimezone;
    
    private $_config = array();
    
    /**
     * Constructor: parses plugins and sets status dir
     *
     * @param string $pluginFile
     * @param string $statusDir
     */
    public function __construct($configFile,$statusDir)
    {
        $statusDir = realpath($statusDir);
        $this->_systemTimezone = Xinc_Timezone::get();
        $this->_statusDir = $statusDir;
        $this->setSystemConfigFile($configFile);
        self::$_instance = &$this;
        
        $this->_apiHandler = Xinc_Api_Handler::getInstance();
    }
    
    public function getSystemTimezone()
    {
        return $this->_systemTimezone;
    }
    /**
     * Return an instance of Xinc_Gui_Handler
     *
     * @return Xinc_Gui_Handler
     */
    public function getInstance()
    {
        return self::$_instance;
    }
    /**
     * Returns the directory where Xinc stores the Project-Statuses
     *
     * @return string
     */
    public function getStatusDir()
    {
        return $this->_statusDir;
    }
    /**
     * Set the plugin.xml file and parse it
     * to load the plugins and register the Widgets with the
     * Xinc_Gui_Widget_Repository
     *
     * @param string $fileName
     */
    private function setSystemConfigFile($fileName)
    {
        $fileName = realpath($fileName);
        try {
            //Xinc_Config::parse($fileName);
        $configFile = Xinc_Config_File::load($fileName);
        
        $this->_configParser = new Xinc_Config_Parser($configFile);
        
        $plugins = $this->_configParser->getPlugins();
        
        $this->_pluginParser = new Xinc_Plugin_Parser();
        
        $this->_pluginParser->parse($plugins);
        
        $widgets = Xinc_Gui_Widget_Repository::getInstance()->getWidgets();
        
        foreach ($widgets as $path => $widget) {
            //echo "Init on: " . get_class($widget) . "\n<br>";
            $widget->init();
        }
        
        $configSettings = $this->_configParser->getConfigSettings();
        while ($configSettings->hasNext()) {
            $setting = $configSettings->next();
            $attributes = $setting->attributes();
            $name = (string)$attributes->name;
            $value = (string)$attributes->value;
            if ($name == 'loglevel' && Xinc_Logger::getInstance()->logLevelSet()) {
                $value = Xinc_Logger::getInstance()->getLogLevel();
            }
            $this->_setConfigDirective($name, $value);
        }
            
        } catch(Exception $e) {
            //var_dump($e);
            Xinc_Logger::getInstance()->error('error parsing system:'
                                             . $e->getMessage());
                
        }
    }
    private function _setConfigDirective($name, $value)
    {
        $this->_config[$name] = $value;
        switch ($name) {
            case 'loglevel':
                Xinc_Logger::getInstance()->setLogLevel($value);
                break;
            case 'timezone':
                Xinc_Timezone::set($value);
                break;
            default:
        }
    }
    public function getConfigDirective($name)
    {
        return isset($this->_config[$name])?$this->_config[$name]:null;
    }
    /**
     * Called from the index.php to generate outpout
     * based on the Request / Widget which is triggered
     *
     */
    public function view()
    {
        /**
         * Determine called Pathname
         */
        $path  = $_SERVER['REDIRECT_URL'];
        
        if (strpos($path, $this->_apiHandler->getBasePath())===0) {
            $this->_apiHandler->processCall($path);
            return;
        }
        /**
         * Get the Widget to use for this Request from the Widget-Repository
         */
        $widget = Xinc_Gui_Widget_Repository::getInstance()->getWidgetForPath($path);
        
        if (!$widget instanceof Xinc_Gui_Widget_Interface ) {
            /**
             * Try Api Handler
             */
            
            header('HTTP/1.0 404 Not Found');
            die;
        }
        /**
         * Start session
         */
        session_start();
        if (!session_is_registered('Xinc_Gui_Handler')) {
           
            $_SESSION['Xinc_Gui_Handler'] = 1;
            /**
             * Trigger the session_start event on the widget
             */
            $widget->handleEvent(Xinc_Gui_Event::SESSION_START);
        }
        /**
         * trigger the page-load event
         */
        $widget->handleEvent(Xinc_Gui_Event::PAGE_LOAD);
        
    }
   
    
}