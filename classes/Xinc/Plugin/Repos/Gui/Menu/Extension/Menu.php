<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Menu Widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Menu.Extension
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

require_once 'Xinc/Plugin/Repos/Gui/Index/Extension/Menu.php';
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';

class Xinc_Plugin_Repos_Gui_Menu_Extension_Menu
    extends Xinc_Plugin_Repos_Gui_Index_Extension_Menu
{
    const TEMPLATE = 'MenuItems={"id":"xinc","text":"Menu","singleClickExpand":true, "children":[%s]};';

    private $_menu;

    private $_extensions;

    public function __construct(Xinc_Plugin_Repos_Gui_Menu_Widget $menu)
    {
        $this->_menu = $menu;
        $this->_extensions = $this->_menu->getExtensions();
    }

    public function getMenuContent()
    {
        $menuItems = array();
        if (isset($this->_extensions['MAIN_MENU_ITEMS'])) {
            foreach ($this->_extensions['MAIN_MENU_ITEMS'] as $extension) {
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

    public function getExtensionPoint()
    {
        return 'MAIN_MENU';
    }
}