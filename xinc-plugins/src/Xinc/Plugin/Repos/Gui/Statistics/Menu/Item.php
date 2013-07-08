<?php
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Menu Widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Statistics.Menu
 * @author    Arno Schneider <username@example.org>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Projects/Extension/Item.php';

class Xinc_Plugin_Repos_Gui_Statistics_Menu_Item
    extends Xinc_Plugin_Repos_Gui_Dashboard_Projects_Extension_Item
{
    private $_widget;

    private $_extensions;

    public function __construct(Xinc_Plugin_Repos_Gui_Statistics_Widget $statWidget)
    {
        $this->_widget = $statWidget;
        $this->_extensions = $this->_widget->getExtensions();
    }

    public function getItem(Xinc_Project $project)
    {
        //$this->_extensions = $this->_widget->getExtensions();
        $numberOfGraphs = 3;
        $graphHeight = 250;
        $statisticsMenu = new Xinc_Plugin_Repos_Gui_Menu_Extension_Item(
            'statistics-' . $project->getName(),
            'Statistics - ' . $project->getName(),
            './statistics/?project=' . $project->getName(),
            'Statistics',
            '',
            true,
            false
        );
        return $statisticsMenu;
    }
}