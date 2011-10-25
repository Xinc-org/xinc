<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Menu Widget which lists all Projects
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Dashboard.Projects.Extension
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

abstract class Xinc_Plugin_Repos_Gui_Dashboard_Projects_Extension_Item
{
    /**
     * Generates a Xinc_Plugin_Repos_Gui_Dashboard_Extension_Item
     *
     * @param Xinc_Project $project
     *
     * @return Xinc_Plugin_Repos_Gui_Menu_Extension_Item
     */
    public abstract function getItem(Xinc_Project $project);
}