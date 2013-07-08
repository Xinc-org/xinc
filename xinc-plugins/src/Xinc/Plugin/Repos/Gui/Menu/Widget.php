<?php
/**
 * Xinc - Continuous Integration.
 * Menu Widget, displays the menu items and the current position
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Menu
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

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';

require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Menu.php';

class Xinc_Plugin_Repos_Gui_Menu_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;

    const TEMPLATE = 'MenuItems={"id":"xinc","text":"Menu","singleClickExpand":true, "children":[%s]};';

    private $_menu = array();

    private $_extensions = array();

    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->_plugin = $plugin;
    }

    public function handleEvent($eventId)
    {
    }

    public function getPaths()
    {
        return array('MENU');
    }

    public function init()
    {
        $indexWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Index_Widget');

        $extension = new Xinc_Plugin_Repos_Gui_Menu_Extension_Menu($this);
        $indexWidget->registerExtension('MAIN_MENU', $extension);
    }

    public function generateMenu()
    {
        $menuItems = array();
        if (isset($this->_extensions['MAIN_MENU_ITEMS'])) {
            foreach ($this->_extensions['MAIN_MENU_ITEMS'] as $extension) {
                //$item = call_user_func_array($extension, array());
                if (!$extension instanceof Xinc_Plugin_Repos_Gui_Menu_Extension_Item) {
                    continue;
                }
                $menuItems[] = $extension->generate();
            } 
        }

        $menuStr = call_user_func_array('sprintf',
                                        array(self::TEMPLATE,implode(",", $menuItems)));
        return $menuStr;
    }

    public function registerExtension($extensionPoint, $extension)
    {
        if (!isset($this->_extensions[$extensionPoint])) {
            $this->_extensions[$extensionPoint] = array();
        }
        $this->_extensions[$extensionPoint][] = $extension;
    }

    public function getExtensions()
    {
        return $this->_extensions;
    }

    public function getExtensionPoints()
    {
        return array('MAIN_MENU_ITEMS');
    }

    public function hasExceptionHandler()
    {
        return false;
    }

    public function handleException(Exception $e)
    {
    }
}