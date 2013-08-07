<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Documentation
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
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Gui/Widget/Abstract.php';
require_once 'Xinc/Gui/Widget/Repository.php';
require_once 'Xinc/Plugin/Repos/Gui/Documentation/Extension/Last.php';

class Xinc_Plugin_Repos_Gui_Documentation_Widget extends Xinc_Gui_Widget_Abstract
{
    public function init()
    {
        $detailWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Detail');

        $extension = new Xinc_Plugin_Repos_Gui_Documentation_Extension_Last();
        $extension->setWidget($this);

        $detailWidget->registerExtension('BUILD_SUMMARY', $extension);

        $dashboardWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Widget');

        $dashboardWidget->registerExtension('PROJECT_FEATURE', $extension);
    }
}
