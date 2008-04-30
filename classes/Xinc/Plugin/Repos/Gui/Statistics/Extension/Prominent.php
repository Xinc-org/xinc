<?php
/**
 * Extension to the Dashboard Widget
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

require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Extension/ProjectInfo.php';
require_once 'Xinc/Plugin/Repos/Documentation.php';

class Xinc_Plugin_Repos_Gui_Statistics_Extension_Prominent
extends Xinc_Plugin_Repos_Gui_Dashboard_Extension_ProjectInfo
{

    public function getTitle()
    {
        return 'Statistics';
    }
    public function getContent(Xinc_Build_Interface &$build)
    {
         
        $projectName = $build->getProject()->getName();
        $url = '/statistics/?project=' . $projectName;
        $click = 'openMenuTab(\'statistics-'.$projectName.'\',\'Statistics - '.$projectName.'\',\''.$url.'\',null,false,true,750);';
        return '<a href="#" onclick="'.$click.'">Graphs</a>';
    }
    public function getExtensionPoint()
    {
        return 'BUILD_SUMMARY';
    }
   
}