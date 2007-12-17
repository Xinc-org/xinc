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

require_once 'Xinc/Plugin/Repos/Gui/Menu/Item.php';


class Xinc_Plugin_Repos_Gui_Statistics_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;
    
    private $_extensions = array();
    
    public $scripts = '';
    
    private $_projectName;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
    }
    
    public function handleEvent($eventId)
    {
       if (isset($_REQUEST['project'])) {
           $this->_projectName = $_REQUEST['project'];
       }
       $url = $_SERVER['REDIRECT_URL'];
       switch($url) {
           //case '/statistics':
           //case '/statistics/':
           //    $src='/statistics/graph/?project=' . $_REQUEST['project'];
           //    include 'templates/iframe.html';
           //    break;
           case '/statistics/graph':
           case '/statistics/graph/':
           default:    
               
               
               include 'templates/graphbase.html';
               break;
       }
       //
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
        return array('/statistics', '/statistics/');
    }
    
    public function getGraphs()
    {
        $project = new Xinc_Project();
        $project->setName($this->_projectName);
        $contents = array();
        if (isset($this->_extensions['STATISTIC_GRAPH'])) {
            foreach ($this->_extensions['STATISTIC_GRAPH'] as $extension) {
                
                $obj = call_user_func_array($extension, array($project));
                
                if ($obj instanceof Xinc_Plugin_Repos_Gui_Statistics_Graph) {
                    $contents[] = $obj->generate();
                }
            }
        }
        return implode("\n", $contents);
    }
    
    public function init()
    {
        $indexWidget = Xinc_Gui_Widget_Repository::getInstance()->
                                                   getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Widget');
        
        $indexWidget->registerExtension('PROJECT_MENU_ITEM', array(&$this,'generateStatisticsMenu'));
        
    }
    
    public function generateStatisticsMenu(Xinc_Project &$project)
    {
        $numberOfGraphs = count($this->_extensions['STATISTIC_GRAPH']);
        $graphHeight = 350;
        $statisticsMenu = new Xinc_Plugin_Repos_Gui_Menu_Item('statistics-' . $project->getName(),
                                                              'Statistics',
                                                              true,
                                                              '/statistics/?project=' . $project->getName(), null,
                                                              'Statistics - ' . $project->getName(),
                                                              true, true, true, $numberOfGraphs*$graphHeight);
        return $statisticsMenu;
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
        return array('STATISTIC_GRAPH');
    }
}