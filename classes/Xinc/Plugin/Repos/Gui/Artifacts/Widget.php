<?php
/**
 * Artifacts Widget, displays the artifacts of a build
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
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';

class Xinc_Plugin_Repos_Gui_Artifacts_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;
    private $_extensions = array();
    public $projects = array();
    
    public $builds;
    
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
        return 'Dashboard';
    }
    public function getPaths()
    {
        return array('ARTIFACTS');
    }
    
    public static function getArtifacts(Xinc_Build_Interface &$build)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $projectName = $build->getProject()->getName();
        $buildTimestamp = $build->getBuildTime();
        
        $detailExtension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension('Artifacts');
        $detailExtension->setContent("TEST");
        
        return $detailExtension;
    }
    
    public function init()
    {
        $detailWidget = Xinc_Gui_Widget_Repository::getInstance()->getWidgetForPath("/dashboard/detail");
        
        $detailWidget->registerExtension('BUILD_DETAILS', array(&$this,'getArtifacts'));
        
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