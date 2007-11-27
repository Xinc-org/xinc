<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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
require_once 'Xinc/Build.php';
require_once 'Xinc/Build/Iterator.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Detail implements Xinc_Gui_Widget_Interface
{
    public $menu;
    protected $_plugin;
    private $_extensions = array();
    private $_internalExtensions = array();
    public $projectName;
    public $project;
    public $build;
    public $logXml;
    public $historyBuilds;
    public $buildTimeStamp;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
    }
    public function registerMainMenu()
    {
        return false;
    }
    private function _generateExternalDetailTabs()
    {
        

        foreach ($this->_extensions['BUILD_DETAILS'] as $extension) { 
            
            $obj = call_user_func_array($extension, array($this->build));
            
        	$this->_registerExtension('BUILD_DETAILS', $obj);
         }
         
    }
    private function _getTemplate($name)
    {
        $dir = dirname(__FILE__);
        $fileName = $dir . DIRECTORY_SEPARATOR . $name;
        return file_get_contents($fileName);
    }
    private function _generateLogDetailTab()
    {
        $i = count($this->logXml->children());
        $rowTpl = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'logDetailsRow.html');
        $rows = array();
        foreach ($this->logXml->children() as $logEntry) { 
            $row = call_user_func_array('sprintf', array($rowTpl, $i--, 
                                                         $logEntry['time'], 
                                                         $logEntry['priority'], 
                                                         $logEntry ));
            $rows[] = $row;
        }
        
        $tabTemplate = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'logDetails.html');
        
        $content = call_user_func_array('sprintf', array($tabTemplate,
                                                         implode("\n", $rows)));
        
        $extension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension('Log Messages');
        $extension->setContent($content);
        
        $this->_registerExtension('BUILD_DETAILS', $extension);
    }
    private function _generateOverviewTab()
    {
        switch ($this->build->getStatus()) {
            case 1:
                $bgColor = 'green';
                break;
            case -1:
                $bgColor = 'gray';
                break;
            case 0:
                $bgColor = 'red';
                break;
            default:
                $bgColor = 'gray';
        }
        
        
        $tabTemplate = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'overviewTab.html');
        $content = call_user_func_array('sprintf', array($tabTemplate,
                                                         $bgColor,
                                                         date('Y-m-d H:i:s', $this->build->getBuildTime()),
                                                         $this->build->getLabel()));
        
        $extension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension('Overview');
        $extension->setContent($content);
        
        $this->_registerExtension('BUILD_DETAILS', $extension);
    }
    private function _generateAllBuildsTab()
    {
        
        
        
        $tabTemplate = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'allbuilds.html');
        $rowTpl = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'allbuildsRow.html');
        $rows = array();
        while ($this->historyBuilds->hasNext()) {
            $build = $this->historyBuilds->next();
            switch ($build->getStatus()) {
                case 1:
                    $bgColor = 'green';
                    break;
                case -1:
                    $bgColor = 'gray';
                    break;
                case 0:
                    $bgColor = 'red';
                    break;
                default:
                    $bgColor = 'gray';
            }
            $rows[] = call_user_func_array('sprintf', array($rowTpl,
                                                            $bgColor,
                                                            $build->getProject()->getName(),
                                                            $build->getBuildTime(),
                                                            $bgColor,
                                                            date('Y-m-d H:i:s', $build->getBuildTime()),
                                                            $bgColor,
                                                            $build->getLabel()));
        }
        
        $content = call_user_func_array('sprintf', array($tabTemplate,
                                                         implode("\n", $rows)));
        
        $extension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension('All Builds');
        $extension->setContent($content);
        
        $this->_registerExtension('BUILD_SELECTOR', $extension);
    }
    public function getTabs($name)
    {
        if (!isset($this->_internalExtensions[$name])) return array();
        return $this->_internalExtensions[$name];
    }
    
    public function handleEvent($eventId)
    {
        $this->projectName = $_GET['project'];
        if (isset($_GET['timestamp'])) {
            $this->buildTimeStamp = $_GET['timestamp'];
        }
        switch ($eventId) {
            case Xinc_Gui_Event::PAGE_LOAD: 
                    
                    $handler = Xinc_Gui_Handler::getInstance();
                    $statusDir = $handler->getStatusDir();
                    $fullStatusDir = $statusDir.DIRECTORY_SEPARATOR .$this->projectName;
                    if ($this->buildTimeStamp != null) {
                        $year = date('Y', $this->buildTimeStamp);
                        $month = date('m', $this->buildTimeStamp);
                        $day = date('d', $this->buildTimeStamp);
                        $fullStatusDir .= DIRECTORY_SEPARATOR .
                                          $year . $month . $day . 
                                          DIRECTORY_SEPARATOR . 
                                          $this->buildTimeStamp;
                    }
                    $statusFile = $fullStatusDir . DIRECTORY_SEPARATOR . 'build.ser';
                    
                    if (!file_exists($fullStatusDir)) {
                        include 'view/detailerror.phtml';
                    } else if (!file_exists($statusFile)) {
                        include 'view/detailerror.phtml';
                    } else {
                        $this->project = new Xinc_Project();
                        $this->project->setName($this->projectName);
                        $this->build = Xinc_Build::unserialize($this->project, $this->buildTimeStamp, Xinc_Gui_Handler::getInstance()->getStatusDir());
                        $buildTime = $this->build->getBuildTime();
                        $year = date('Y', $buildTime);
                        $month = date('m', $buildTime);
                        $day = date('d', $buildTime);
                        if ($this->buildTimeStamp == null) {
                            $detailDir = $statusDir.DIRECTORY_SEPARATOR .$this->projectName;
                            $year = date('Y', $this->build->getBuildTime());
                            $month = date('m', $this->build->getBuildTime());
                            $day = date('d', $this->build->getBuildTime());
                            $detailDir .= DIRECTORY_SEPARATOR .
                                          $year . $month . $day . 
                                          DIRECTORY_SEPARATOR . 
                                          $this->build->getBuildTime();
                        } else {
                            $detailDir = $fullStatusDir;
                        }
                        $logXmlFile = $detailDir.DIRECTORY_SEPARATOR.'buildlog.xml';
                        
                        if (file_exists($logXmlFile)) {
                            $this->logXml = new SimpleXMLElement(file_get_contents($logXmlFile));
                            
                        } else {
                            $this->logXml = new SimpleXmlElement('<log/>');
                        }
                        
                        
                        /**
                         * get History Builds
                         */
                        $this->historyBuilds = $this->getHistoryBuilds($statusDir);
                        
                        /**
                         * Generate the build selector on the right
                         */
                        $this->_generateAllBuildsTab();
                        /**
                         * Overview info tab
                         */
                        $this->_generateOverviewTab();
                        /**
                         * Generate the tab for the log messages
                         */
                        $this->_generateLogDetailTab();
                        /**
                         * Generate the external tabs that were registered through a hook
                         */
                        $this->_generateExternalDetailTabs();
                        
                        $this->menu = '';
                        foreach ($this->_extensions['MAIN_MENU'] as $extension) {
                            
                            $this->menu .= call_user_func_array($extension, array($this,
                                                                                  'Details for ' .
                                                                                  $this->build->getProject()->getName()));
                            
                        }
                        include 'view/projectDetail.phtml';
                    }
                    
                break;
            default:
                break;
        }
    }
    
    private function getHistoryBuilds($statusDir)
    {
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $this->projectName . '.history';
        
        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $builds = array();
        
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize($this->project,
                                                       $buildTimestamp,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                $builds[] = $buildObject;
            } catch (Exception $e) {
                // TODO: Handle
            }
            
        }
        $builds = array_reverse($builds);
        return new Xinc_Build_Iterator($builds);
    }
    
    public function getTitle()
    {
        return 'Dashboard';
    }
    public function getPaths()
    {
        return array('/dashboard/detail', '/dashboard/detail/');
    }
    public function init()
    {
        
    }
    
    private function _registerExtension($extensionPoint,
                                        Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension &$detail)
    {
        if (!isset($this->_internalExtensions[$extensionPoint])) {
            $this->_internalExtensions[$extensionPoint] = array();
        }
        $this->_internalExtensions[$extensionPoint][] = $detail;
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
        return array('BUILD_DETAILS');
    }
}