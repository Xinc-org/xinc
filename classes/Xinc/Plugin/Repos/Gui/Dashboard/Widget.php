<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc
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

class Xinc_Plugin_Repos_Gui_Dashboard_Widget implements Xinc_Gui_Widget_Interface
{
    private $_plugin;
    private $_widgets = array();
    public $projects=array();
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
    }
    
    public function handleEvent($eventId)
    {
        switch ($eventId) {
            case Xinc_Gui_Event::PAGE_LOAD: 
                    
                    $handler=Xinc_Gui_Handler::getInstance();
                    $statusDir=$handler->getStatusDir();
                    $dir=opendir($statusDir);
                    while ($file=readdir($dir)) {
                        $project=array();
                        $fullfile=$statusDir.DIRECTORY_SEPARATOR.$file;
                        
                        if (!in_array($file, array('.', '..')) && is_dir($fullfile)) {
                            $project['name']=$file;
                            $statusfile=$fullfile.DIRECTORY_SEPARATOR.'status.ser';
                            $xincProject=$fullfile.DIRECTORY_SEPARATOR.'.xinc';
                            
                            if (file_exists($statusfile) && file_exists($xincProject)) {
                                $ini=parse_ini_file($statusfile, true);
                                
                                $project['build.status']=$ini['build.status'];
                                $project['build.label']= isset($ini['build.label'])?$ini['build.label']:'';
                                $project['build.time']=$ini['build.time'];
                                $this->projects[]=$project;
                            } else if (file_exists($xincProject)) {
                                $project['build.status'] = -10;
                                $project['build.time'] = 0;
                                $project['build.label'] = '';
                                $this->projects[]=$project;
                            }
                            
                            
                            
                        }
                    }
                    include 'view/overview.php';
                    
                break;
            default:
                break;
        }
    }
    public function registerMainMenu()
    {
        return true;
    }
    public function getTitle()
    {
        return 'Dashboard';
    }
    public function getPaths()
    {
        return array('/dashboard', '/dashboard/');
    }
    
    public function registerWidget(Xinc_Gui_Widget_Interface &$widget)
    {
        $this->_widgets[] = $widget;
    }
}