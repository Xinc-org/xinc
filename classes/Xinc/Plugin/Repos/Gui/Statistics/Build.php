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
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph.php';


class Xinc_Plugin_Repos_Gui_Statistics_Build implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;
    
    private $_extensions = array();
    
    public $scripts = '';
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
    }
    
    public function handleEvent($eventId)
    {
       
    }


    public function getPaths()
    {
        return array();
    }
    
    public function getBuildDurationGraph(Xinc_Project &$project)
    {
        
        $graph = new Xinc_Plugin_Repos_Gui_Statistics_Graph('Build Duration in seconds', 'line', '#f2f2f2', 'blue');
        return $graph;
    }
    private function _getHistoryBuilds(Xinc_Project &$project, $start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $project->getName() . '.history';
        
        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $totalCount = count($buildHistoryArr);
        if ($limit==null) {
            $limit = $totalCount;
        }
        /**
         * turn it upside down so the latest builds appear first
         */
        $buildHistoryArr = array_reverse($buildHistoryArr, true);
        $buildHistoryArr = array_slice($buildHistoryArr, $start, $limit, true);
        
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
        $statisticWidget = Xinc_Gui_Widget_Repository::getInstance()->
                                                       getWidgetByClassName('Xinc_Plugin_Repos_Gui_Statistics_Widget');
        
        $statisticWidget->registerExtension('STATISTIC_GRAPH', $this->getBuildDurationGraph());
        
    }
    
   
    public function registerExtension($extensionPoint, Xinc_Gui_Widget_Extension_Interface &$extension)
    {
       
        
    }
    public function getExtensionPoints()
    {
        return array();
    }
}