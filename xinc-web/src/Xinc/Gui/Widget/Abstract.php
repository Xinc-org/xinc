<?php
/**
 * Xinc - Continuous Integration.
 * Interface for a gui widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Gui.Widget
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2013 Alexander Opitz, Leipzig
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

require_once 'Xinc/Gui/Widget/Interface.php';

abstract class Xinc_Gui_Widget_Abstract implements Xinc_Gui_Widget_Interface
{
    /**
     * The bundled plugin
     *
     * @var Xinc_Plugin_Interface
     */
    protected $plugin;

    /**
     * True if this Widget handles exceptions self.
     *
     * @var boolean
     */
    protected $hasExceptionHandler = false;


    /**
     * List of Sub Extensions
     *
     * @var array
     */
    protected $extensions = array();

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
    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->plugin = $plugin;
    }
    /**
     * The Xinc_Gui_Handler fires different events on the
     * Widgets (see Xinc_Gui_Event)
     * 
     * The Widget can react differently on the events.
     * A normal page load-event is Xinc_Gui_Event::PAGE_LOAD
     *
     * @param integer $eventId
     *
     * @return void
     */
    public function handleEvent($eventId)
    {
    }

    /**
     * returns the Pathnames for which the Widget wants to register itself for execution
     *
     * getPaths() returns array('/dashboard', '/olddashboard');
     * Widget is called for: 
     *   http://mydomain.com/dashboard
     *     AND
     *   http://mydomain.com/olddashboard
     *     BUT NOT FOR
     *   http://mydomain.com/dashboard/
     *
     * @return array
     */
    public function getPaths()
    {
        return array();
    }

    /**
     * Is called after all widgets have been registered. This is the place where widgets need
     * to register the hooks for another Widget
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * get the defined hooks of this widget
     * 
     * Hooks can be used to allow other widgets to extend this widget
     *
     * @return array
     */
    public function getExtensionPoints()
    {
        return array();
    }

    /**
     * Register an extensions
     *
     * @param string $extensionPoint
     * @param Xinc_Gui_Widget_Extension $extension extension
     *
     * @return void
     */
    public function registerExtension($extensionPoint, $extension)
    {
        if (!isset($this->extensions[$extensionPoint])) {
            $this->extensions[$extensionPoint] = array();
        }
        $this->extensions[$extensionPoint][] = $extension;
    }

    /**
     * Returns the registered extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns if this widget handles exceptions themself.
     *
     * @return boolean
     */
    public function hasExceptionHandler()
    {
        return $this->hasExceptionHandler;
    }

    /**
     * Handles an exception while view.
     *
     * @param Exception $e The Exception to handle
     *
     * @return void
     */
    public function handleException(Exception $e) {
        if ($this->hasExceptionHandler) {
            throw new Exception('Widget has no exception handler but needs to handle an exception');
        }
    }
}
