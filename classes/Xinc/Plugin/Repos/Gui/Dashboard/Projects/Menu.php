<?php
/**
 * Extension to the Dashboard Menu Widget which lists all Projects
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
require_once 'Xinc/Plugin/Repos/Gui/Menu/Item.php';
require_once 'Xinc/Build/Iterator.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Projects_Menu extends Xinc_Plugin_Repos_Gui_Menu_Item
{
    
    private $_subMenus = array();
    
    public function registerSubExtension($extension)
    {
       
        $this->_subMenus[] = $extension;
    }
    
    protected function _generateChildren()
    {
        $builds = new Xinc_Build_Iterator();
        
        $handler = Xinc_Gui_Handler::getInstance();
        $statusDir = $handler->getStatusDir();
        $dir = opendir($statusDir);
        while ($file = readdir($dir)) {
            $fullfile = $statusDir . DIRECTORY_SEPARATOR . $file;
            
            if (!in_array($file, array('.', '..')) && is_dir($fullfile)) {

                $statusfile = $fullfile . DIRECTORY_SEPARATOR . 'build.ser';
                
                if (file_exists($statusfile)) {

                    $object = unserialize(file_get_contents($statusfile));
                    $builds->add($object);
                }
                
            }
        }
        $projects = array();
        while ($builds->hasNext()) {
            $build = $builds->next();
            /**
             * Do we have children?
             */
            
            $children = array();
            foreach ($this->_subMenus as $sub) {
                $subExtension = call_user_func_array($sub, array($build->getProject()));
                $children[] = $subExtension->generate();
            }
            
            $item = new Xinc_Plugin_Repos_Gui_Menu_Item('project-' . $build->getProject()->getName()
                                                        . '-' . $build->getBuildTime(),
                                                        $build->getProject()->getName(),
                                                        true,
                                                        '/dashboard/detail?project='
                                                        . $build->getProject()->getName()
                                                        . '&timestamp=' . $build->getBuildTime(),
                                                        null,
                                                        $build->getLabel(). ' - ' . $build->getProject()->getName(),
                                                        true,
                                                        count($children)>0 ? false: true,
                                                        false,
                                                        'auto',
                                                        $children);
            $projects[] = $item->generate();
        }
        return implode(',', $projects);
    }
}