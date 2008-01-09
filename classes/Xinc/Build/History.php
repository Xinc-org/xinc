<?php
/**
 * Build History retrieves the buildtimes of a project
 * 
 * @package Xinc.Build
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

require_once 'Xinc/Project.php';
require_once 'Xinc.php';

class Xinc_Build_History
{
    /**
     * returns an array of build timestamps for a project
     *
     * @param Xinc_Project $project
     * @return array
     */
    public static function get(Xinc_Project &$project)
    {
        $projectName = $project->getName();
        
        if (class_exists('Xinc_Gui_Handler')) {
            // we are in gui mode
            $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        } else {
            $statusDir = Xinc::getInstance()->getStatusDir();
        }
        
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        if (file_exists($historyFile)) {
            $buildHistoryArr = @unserialize(file_get_contents($historyFile));
        } else {
            $buildHistoryArr = array();
        }
        
        return $buildHistoryArr;
    }
}