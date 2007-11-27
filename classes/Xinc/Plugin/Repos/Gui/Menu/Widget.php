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

    private $_menu = array();
    
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
        $widgets = Xinc_Gui_Widget_Repository::getInstance()->getWidgets();
        $classes = array();
        foreach ($widgets as $widget) {
            if ($widget->registerMainMenu()) {
                $this->_menu[] = $widget;
                
                
            } 
            $widget->registerExtension('MAIN_MENU', array(&$this,'getMenu'));
            
            
        }
        
        
        
    }
    public function registerExtension($extension, $callback)
    {
        $this->_extensions[$extension] = $callback;
    }
    public function getExtensionPoints()
    {
        return array();
    }
}