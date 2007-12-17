<?php
/**
 * Menu Widget, displays the menu items and the current position
 * 
 * @package Xinc.Plugin
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

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';


class Xinc_Plugin_Repos_Gui_Menu_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;
    
    const TEMPLATE = 'MenuItems={"id":"xinc","text":"Menu","singleClickExpand":true, "children":[%s]};';

    private $_menu = array();
    
    private $_extensions = array();
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
    }
    
    public function handleEvent($eventId)
    {
       
    }
    public function registerMainMenu()
    {
        return false;
    }
    public function getTitle()
    {
        return 'Menu';
    }
    public function getPaths()
    {
        return array('MENU');
    }
    
    private function _getTemplate($name)
    {
        $dir = dirname(__FILE__);
        $fileName = $dir . DIRECTORY_SEPARATOR . $name;
        return file_get_contents($fileName);
    }
    public function getMenu(Xinc_Gui_Widget_Interface &$widget, $position)
    {
        $name = $widget->getTitle();
        $menuArr = array();
        $menuTpl = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'menu.html');
        $menuItemTpl = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'menuItem.html');
        foreach ($this->_menu as $widgetItem) {
            $paths = $widgetItem->getPaths();
            if (get_class($widget) != get_class($widgetItem)) {
                $menuStr = call_user_func_array('sprintf', array($menuItemTpl, $paths[0], $widgetItem->getTitle()));
                $menuArr[] = $menuStr;
            }
        }
        
        $content = str_replace(array('{here}','{rows}'), array($position, implode("\n", $menuArr)), $menuTpl);
        return $content;
    }
    
    public function init()
    {
        $indexWidget = Xinc_Gui_Widget_Repository::getInstance()->
                                                   getWidgetByClassName('Xinc_Plugin_Repos_Gui_Index_Widget');
        
        $indexWidget->registerExtension('MAIN_MENU', array(&$this,'generateMenu'));
        
    }
    
    public function generateMenu()
    {
        $menuItems = array();
        if (isset($this->_extensions['MAIN_MENU_ITEMS'])) {
            foreach ($this->_extensions['MAIN_MENU_ITEMS'] as $extension) {
                
                $item = call_user_func_array($extension, array());
                
                if (!$item instanceof Xinc_Plugin_Repos_Gui_Menu_Item) {
                    continue;
                }
                $menuItems[] = $item->generate();
            } 
        }
        
        $menuStr = call_user_func_array('sprintf',
                                        array(self::TEMPLATE,implode(",", $menuItems)));
        return $menuStr;
    }
    
    public function registerExtension($extension, $callback)
    {
        
        if (!isset($this->_extensions[$extension])) {
            $this->_extensions[$extension] = array();
        }
        $this->_extensions[$extension][] = $callback;
    }
    public function getExtensionPoints()
    {
        return array('MAIN_MENU_ITEMS');
    }
}