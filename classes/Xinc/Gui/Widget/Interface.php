<?php
/**
 * PUT DESCRIPTION HERE
 * 
 * @package Xinc.Gui
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
interface Xinc_Gui_Widget_Interface
{
    /**
     * Constructor for a Widget
     * 
     * The plugin passed itself as a variable
     * to the constructor.
     * 
     * The Widget can access the plugins shared functionality
     *
     * @param Xinc_Plugin_Interface $plugin
     */
    public function __construct(Xinc_Plugin_Interface &$plugin);
    
    /**
     * The Xinc_Gui_Handler fires different events on the
     * Widgets (see Xinc_Gui_Event)
     * 
     * The Widget can react differently on the events.
     * A normal page load-event is Xinc_Gui_Event::PAGE_LOAD
     *
     * @param integer $eventId
     */
    public function handleEvent($eventId);
    
    /**
     * returns the Pathnames for which the Widget 
     * wants to register itself for execution
     * 
     * getPaths() returns array('/dashboard', '/olddashboard');
     * Widget is called for: 
     *   http://mydomain.com/dashboard
     *     AND
     *   http://mydomain.com/olddashboard
     *     BUT NOT FOR
     *   http://mydomain.com/dashboard/
     *
     */
    public function getPaths();
    
    
    /**
     * Is called after all widgets have
     * been registered. This is the place where widgets need 
     * to register the hooks for another Widget
     *
     */
    public function init();
    
    /**
     * get the defined hooks of this widget
     * 
     * Hooks can be used to allow other widgets to
     * extend this widget
     * @return array
     */
    public function getExtensionPoints();
    
    /**
     * Register an extensions
     *
     * @param string $extension name of the extension point
     * @param array $callback Needs to be executable by call_user_func_array
     */
    public function registerExtension($extension, $callback);
    
    /**
     * return true if the widget should be registered in the
     * main menu.
     * if it is a "sub-widget" return false
     *
     * @return boolean whether to register the widget in the main-menu
     *
     */
    public function registerMainMenu();
    
    /**
     * Returns the name of the widget as
     * it should be displayed in any menu / title
     *
     */
    public function getTitle();
    
}
