<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin
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

require_once 'Xinc/Plugin/Interface.php';

abstract class Xinc_Plugin_Base implements Xinc_Plugin_Interface
{
    /**
     *
     * @return Xinc_Api_Module_Interface[]
     */
    public function getApiModules()
    {
        return array();
    }

    /**
     *
     * @return Xinc_Gui_Widget_Interface[]
     */
    public function getGuiWidgets()
    {
        return array();
    }

    /**
     * Returns the defined tasks of the plugin
     *
     * @return Xinc_Plugin_Task[]
     */
    public function getTaskDefinitions()
    {
        return array();
    }
    
    /**
     * Validate if the plugin can run properly on this system
     *
     * @return boolean True if plugin can run properly otherwise false.
     */
    public function validate()
    {
        return true;
    }
}