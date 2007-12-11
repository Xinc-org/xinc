<?php
/**
 * The Widget Repository holds all the Widgets
 * that are defined by the loaded plugins.
 * 
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

/**
 * The Widget-Repository allows the Web-Frontend of Xinc to choose the right
 * plugin for execution based on the Http-Request
 * @package Xinc.Gui
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 * @version    2.0
 * @author     Arno Schneider
 * @since      Class available since Release 2.0
 */
class Xinc_Gui_Widget_Repository
{
    /**
     * @var Xinc_Gui_Widget_Repository
     */
    private static $_instance;
    /**
     * @var Xinc_Gui_Widget_Interface[]
     */
    private $_definedWidgets = array();
    
    /**
     * Contains all the registered widgets
     *
     * @var Xinc_Gui_Widget_Interface[]
     */
    private $_widgets = array();
    
    /**
     * Return an instance of the Widget Repository
     *
     * @return Xinc_Gui_Widget_Repository
     */
    public static function getInstance()
    {
        if (!Xinc_Gui_Widget_Repository::$_instance) {
            Xinc_Gui_Widget_Repository::$_instance = new Xinc_Gui_Widget_Repository();
        }
        return Xinc_Gui_Widget_Repository::$_instance;
    }
    
    /**
     * Register a widget with the Repository
     *
     * @param Xinc_Gui_Widget_Interface $widget
     */
    public function registerWidget(Xinc_Gui_Widget_Interface &$widget)
    {
        /**
         *  Determine the Pathnames which
         * the Widget will be called for
         */
        $paths = $widget->getPaths();
        foreach ($paths as $path) {
            /**
             * register the widget for the specified pathname
             */
            $this->_definedWidgets[$path] = $widget;
        }
        $this->_widgets[] = $widget;
        
    }
    /**
     * Determines the Widget that should be used
     * for the specified Http-Request by the Pathname that 
     * is called
     *
     * @param String $path Pathname of the HTTP-Request
     * @return Xinc_Gui_Widget_Interface
     */

    public function &getWidgetForPath($path)
    {
        
        $widget = null;
        if (!isset($this->_definedWidgets[$path])) {
            // find the largest match
            $largest = 0;
            foreach ($this->_definedWidgets as $pathReg => $widgetItem) {
                
                if (($match = strstr($path, $pathReg)) !== false && strpos($path, $pathReg)==0) {
                    if (strlen($pathReg)>$largest) {
                        
                        $largest = strlen($pathReg);
                        $widget = $widgetItem;
                    }
                }
            }
        } else {
            $widget = $this->_definedWidgets[$path];
        }
        return $widget;
    }
    
    /**
     * Returns all the registered Widgets
     *
     * @return Xinc_Gui_Widget_Interface[]
     */
    public function getWidgets()
    {
        return $this->_widgets;
    }
}