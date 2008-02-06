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
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Menu/Item.php';
require_once 'Xinc/Data/Repository.php';
require_once 'Xinc/Build/History.php';
require_once 'Xinc/Build/Repository.php';


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
               
               
               include Xinc_Data_Repository::getInstance()->get('templates' . DIRECTORY_SEPARATOR
                                                               . 'statistics' . DIRECTORY_SEPARATOR
                                                               . 'graphbase.phtml');
               break;
       }
       //
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
                
                
                //$obj = call_user_func_array($extension, array($project));
                
                //if ($obj instanceof Xinc_Plugin_Repos_Gui_Statistics_Graph) {
                $contents[] = $extension->generate(array('Build duration in seconds' =>
                                                         $this->_getHistoryBuilds($project, 0, 15)
                                                        ), array('#1c4a7e','#bb5b3d'));
                //}
            }
        }
        return implode("\n", $contents);
    }
    private function _getHistoryBuilds(Xinc_Project &$project, $start, $limit=null)
    {
        /**$statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $project->getName() . '.history';
        
        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $totalCount = count($buildHistoryArr);
        if ($limit==null) {
            $limit = $totalCount;
        }*/
        /**
         * turn it upside down so the latest builds appear first
         */
        /**$buildHistoryArr = array_reverse($buildHistoryArr, true);
        $buildHistoryArr = array_slice($buildHistoryArr, $start, $limit, true);*/
        $buildHistoryArr = Xinc_Build_History::getFromTo($project, $start, $limit);
        $totalCount = Xinc_Build_History::getCount($project);
        
        $builds = array();
        
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize($project,
                                                       $buildTimestamp,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                $builds[] = array('number'=>$buildObject->getNumber(),
                                  'y'=>$buildObject->getStatistics()->get('build.duration'),
                                  'xlabel'=>$buildObject->getNumber());
            } catch (Exception $e) {
                // TODO: Handle
               
                
            }
            
        }
        
        $builds = array_reverse($builds, true);
        
        return $builds;
    }
    public function init()
    {
        $indexWidget = Xinc_Gui_Widget_Repository::getInstance()->
                                                   getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Widget');
        $extension = new Xinc_Plugin_Repos_Gui_Statistics_Menu_Item($this);
        
        $indexWidget->registerExtension('PROJECT_MENU_ITEM', $extension);
        
    }
    
    public function generateStatisticsMenu(Xinc_Project &$project)
    {
        $numberOfGraphs = count($this->_extensions['STATISTIC_GRAPH']);
        $graphHeight = 350;
        $statisticsMenu = new Xinc_Plugin_Repos_Gui_Menu_Extension_Item('statistics-' . $project->getName(),
                                                              'Statistics',
                                                              true,
                                                              '/statistics/?project=' . $project->getName(), null,
                                                              'Statistics - ' . $project->getName(),
                                                              true, true, true, $numberOfGraphs*$graphHeight);
        return $statisticsMenu;
    }
    
    public function registerExtension($extensionPoint, &$extension)
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
        return array('STATISTIC_GRAPH');
    }
}