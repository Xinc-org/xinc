<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Deliverable
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

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';
require_once 'Xinc/Plugin/Repos/Gui/Artifacts/Extension/Dashboard.php';
require_once 'Xinc/Plugin/Repos/Gui/Deliverable/Extension/Last.php';
require_once 'Xinc/Data/Repository.php';

class Xinc_Plugin_Repos_Gui_Deliverable_Widget implements Xinc_Gui_Widget_Interface
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
    }

    public function getPaths()
    {
        return array();
    }

    public function init()
    {
        $detailWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Detail');

        $extension = new Xinc_Plugin_Repos_Gui_Deliverable_Extension_Last();
        $extension->setWidget($this);

        $detailWidget->registerExtension('BUILD_SUMMARY', $extension);

        $dashboardWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Widget');

        $dashboardWidget->registerExtension('PROJECT_FEATURE', $extension);
    }

    public function registerExtension($extensionPoint, &$extension)
    {
        $this->_extensions[$extensionPoint] = $extension;
    }

    public function getExtensionPoints()
    {
        return array();
    }

    public function hasExceptionHandler()
    {
        return false;
    }

    public function handleException(Exception $e)
    {
    }
}