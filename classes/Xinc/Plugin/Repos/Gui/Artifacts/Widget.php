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
       global $_GET;
       
       $project = new Xinc_Project();
       $project->setName($_GET['p']);
       try {
           $build = Xinc_Build::unserialize($project, $_GET['buildtime'], Xinc_Gui_Handler::getInstance()->getStatusDir());
           $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
           $statusDir .= DIRECTORY_SEPARATOR . $build->getStatusSubDir() . 
                         DIRECTORY_SEPARATOR . Xinc_Plugin_Repos_Artifacts::ARTIFACTS_DIR .
                         DIRECTORY_SEPARATOR;
           $file = $_GET['file'];
           $file = str_replace('../', '/', $file);
           $file = str_replace('/..', '/', $file);
           $fileName = $statusDir.$file;
           if (file_exists($fileName)) {
               header("Content-Type: " . mime_content_type($fileName));
               readfile($fileName);
           } else {
               echo "Could not find artifact";
           }
           
       } catch (Exception $e) {
           echo "Could not find any artifacts";
       }
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
        return array('/artifacts/get', '/artifacts/get/');
    }
    
    private function _walkDir(Xinc_Build_Interface &$build, $dirname, &$arr, &$treeItems, $level=0)
    {
        $projectName = $build->getProject()->getName();
        $buildTime = $build->getBuildTime();
        $safeDirName = preg_replace('/\W/', '_', $dirname);
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $statusDir .= DIRECTORY_SEPARATOR . $build->getStatusSubDir() . 
                      DIRECTORY_SEPARATOR . Xinc_Plugin_Repos_Artifacts::ARTIFACTS_DIR .
                      DIRECTORY_SEPARATOR;
        if (!is_dir($dirname)) return;
        $dh = opendir($dirname);
        if ($level==0) {
            $template = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'treeAddItem.html');
        } else {
            $template = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'treeItemAddItem.html');
        }
        if ($dh) {
            while ($file = readdir($dh)) {
                if (!in_array($file, array('.', '..'))) {
                    if (is_dir($dirname . DIRECTORY_SEPARATOR . $file)) {
                        $safeFileDirname = $dirname . DIRECTORY_SEPARATOR . $file;
                        $safeFileDirname = preg_replace('/\W/', '_', $safeFileDirname);
                        $arr[$file] = array();
                        if ($level == 0) {
                            $params = array($template, 
                                            $safeFileDirname,
                                            $dirname . DIRECTORY_SEPARATOR . $file,
                                            '#',
                                            $safeFileDirname);
                        } else {
                            $params = array($template, 
                                            $safeFileDirname,
                                            $dirname . DIRECTORY_SEPARATOR . $file,
                                            '#',
                                            $safeDirName,
                                            $safeFileDirname);
                        }
                        $result = call_user_func_array('sprintf', $params);
                        $treeItems[] = $result;
                        $this->_walkDir($build, $dirname . DIRECTORY_SEPARATOR . $file, $arr[$file], $level++);
                    } else {
                        $safeFileName = $dirname . DIRECTORY_SEPARATOR . $file;
                        $safeFileName = preg_replace('/\W/', '_', $safeFileName);
                        $artifactsFile = str_replace($statusDir, '', $dirname . DIRECTORY_SEPARATOR . $file);
                        if ($level == 0 ){
                            $params = array($template, 
                                            $safeFileName,
                                            $file,
                                            '/artifacts/get/?p=' . 
                                                                 $projectName .
                                                                 '&buildtime=' .
                                                                 $buildTime .
                                                                 '&file=' .
                                                                 $artifactsFile,
                                            $safeFileName);
                        } else {
                            $params = array($template, 
                                            $safeFileName,
                                            $file,
                                            '/artifacts/get/?p=' . 
                                                                 $projectName .
                                                                 '&buildtime=' .
                                                                 $buildTime .
                                                                 '&file=' .
                                                                 $artifactsFile,
                                            $safeDirName,
                                            $safeFileName);
                        }
                        $result = call_user_func_array('sprintf', $params);
                        
                        $arr[] = $file;
                        $treeItems[] = $result;
                    }
                }
            }
        }
    }
    
    private function _getArtifactsTree(Xinc_Build_Interface &$build)
    {
        $dir = $this->_plugin->getArtifactsDir($build);
        
        $treeStructure = array();
        $treeItems = array();
        
        $this->_walkDir($build, $dir, $treeStructure, $treeItems);
        
        $baseTemplate = $this->_getTemplate('templates' . DIRECTORY_SEPARATOR . 'tree.html');
        $result = str_replace('{items}', implode("\n", $treeItems), $baseTemplate);
        return $result;
    }
    private function _getTemplate($name)
    {
        $dir = dirname(__FILE__);
        $fileName = $dir . DIRECTORY_SEPARATOR . $name;
        return file_get_contents($fileName);
    }
    public function getArtifacts(Xinc_Build_Interface &$build)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $projectName = $build->getProject()->getName();
        $buildTimestamp = $build->getBuildTime();
        
        $detailExtension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension('Artifacts');
        $detailExtension->setContent($this->_getArtifactsTree($build));
        
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