<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Interface for a gui widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Gui.Widget
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

require_once 'Xinc/Gui/Widget/Extension/Interface.php';

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
     * @param string $extensionPoint
     * @param Xinc_Gui_Widget_Extension $extension extension
     */
    public function registerExtension($extensionPoint, &$extension);
    
    /**
     * @return boolean
     */
    public function hasExceptionHandler();
    
    /**
     * @param Exception $e
     */
    public function handleException(Exception $e);
}
