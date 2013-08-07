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
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Gui/Widget/Abstract.php';
require_once 'Xinc/Gui/Widget/Repository.php';
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Menu.php';
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';

class Xinc_Plugin_Repos_Gui_Menu_Widget extends Xinc_Gui_Widget_Abstract
{
    const TEMPLATE = 'MenuItems={"id":"xinc","text":"Menu","singleClickExpand":true, "children":[%s]};';

    private $_menu = array();

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

        $menuStr = call_user_func_array('sprintf', array(self::TEMPLATE,implode(",", $menuItems)));
        return $menuStr;
    }

    public function getExtensionPoints()
    {
        return array('MAIN_MENU_ITEMS');
    }
}
