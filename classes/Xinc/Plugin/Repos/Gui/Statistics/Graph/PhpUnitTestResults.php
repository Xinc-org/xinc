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


class Xinc_Plugin_Repos_Gui_Statistics_Graph_PhpUnitTestResults extends Xinc_Plugin_Repos_Gui_Statistics_Graph
{
    
    public function buildDataSet(Xinc_Project &$project, array $buildHistoryArr = array(), $previousData = array())
    {
        if (count($previousData)>0) {
            $data = $previousData;
        } else {
            $data = array('Number of Tests'=>array(),'Passed tests'=>array(),'Failed tests'=>array());
        }
        
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize($project,
                                                       $buildTimestamp,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                                                 
                if ($buildObject->getStatistics()->get('phpunit.numberOfTests')>0) {
                    $data['Number of Tests'][$buildObject->getNumber()] = $buildObject->getStatistics()->get('phpunit.numberOfTests');
                    $data['Failed tests'][$buildObject->getNumber()] = $buildObject->getStatistics()->get('phpunit.numberOfFailures');
                    $data['Passed tests'][$buildObject->getNumber()] = $buildObject->getStatistics()->get('phpunit.numberOfTests') - $buildObject->getStatistics()->get('phpunit.numberOfFailures');
                } else {
                    $data['Number of Tests'][$buildObject->getNumber()] = 0;
                    $data['Failed tests'][$buildObject->getNumber()] = 0;
                    $data['Passed tests'][$buildObject->getNumber()] = 0;
                }
                unset($buildObject);
            } catch (Exception $e) {
                // TODO: Handle
               var_dump($e);
                
            }
            
        }
        
        
        return $data;
    }
}