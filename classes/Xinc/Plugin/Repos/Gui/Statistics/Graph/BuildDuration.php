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


require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph.php';


class Xinc_Plugin_Repos_Gui_Statistics_Graph_BuildDuration extends Xinc_Plugin_Repos_Gui_Statistics_Graph
{
    
    public function buildDataSet(Xinc_Project &$project, array $buildHistoryArr = array(), $previousData = array())
    {
        if (count($previousData)>0) {
            $data = $previousData;
        } else {
            $data = array('Successful Builds'=>array(),'Failed Builds'=>array());
        }
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize($project,
                                                       $buildTimestamp,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                $duration = $buildObject->getStatistics()->get('build.duration');
                if ($duration < 1) {
                    $duration = 0;
                }
                if ($buildObject->getStatus() == Xinc_Build_Interface::PASSED) {
                    $data['Successful Builds'][$buildObject->getNumber()] = $duration;
                    $data['Failed Builds'][$buildObject->getNumber()] = 0;
                } else {
                    $data['Failed Builds'][$buildObject->getNumber()] = $duration;
                    $data['Successful Builds'][$buildObject->getNumber()] = 0;
                }
                
                unset($buildObject);
            } catch (Exception $e) {
                // TODO: Handle
               //var_dump($e);
                
            }
            
        }
        return $data;
    }
}