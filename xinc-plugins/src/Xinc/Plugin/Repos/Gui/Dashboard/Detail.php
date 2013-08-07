<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Dashboard
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
require_once 'Xinc/Build.php';
require_once 'Xinc/Build/Iterator.php';

require_once 'Xinc/Data/Repository.php';

require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension/Summary.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension/Log.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension/Builds.php';
require_once 'Xinc/Build/History.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Detail extends Xinc_Gui_Widget_Abstract
{
    private $_internalExtensions = array();

    public $projectName;

    public $project;

    public $build;

    public $logXml;

    public $historyBuilds;

    public $buildTimeStamp;

    private function _generateExternalExtensions()
    {
        foreach ($this->extensions['BUILD_DETAILS'] as $extension) { 
            //$obj = call_user_func_array($extension, array($this->build));
            $this->_registerExtension('BUILD_DETAILS', $extension);
        }
    }

    private function _getTemplate($name)
    {
        $dir = dirname(__FILE__);
        $fileName = $dir . DIRECTORY_SEPARATOR . $name;
        return file_get_contents($fileName);
    }

    private function _generateLogView()
    {
        $extension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension_Log();
        $this->_registerExtension('BUILD_DETAILS', $extension);
    }

    private function _generateSummaryView()
    {
        $extension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension_Summary();
        foreach ($this->extensions['BUILD_SUMMARY'] as $ext) {
            $extension->registerDetailExtension($ext);
        }
        $this->_registerExtension('BUILD_DETAILS', $extension);
    }

    private function _generateBuildsView()
    {
        $extension = new Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension_Builds();
        $this->_registerExtension('BUILD_SELECTOR', $extension);
    }

    public function getTabs($name)
    {
        if (!isset($this->_internalExtensions[$name])) return array();
        return $this->_internalExtensions[$name];
    }

    public function handleEvent($eventId)
    {
        $this->projectName = $_GET['project'];
        if (isset($_GET['timestamp'])) {
            $this->buildTimeStamp = $_GET['timestamp'];
        }
        $this->project = new Xinc_Project();
        $this->project->setName($this->projectName);
        switch ($eventId) {
            case Xinc_Gui_Event::PAGE_LOAD:
                $handler = Xinc_Gui_Handler::getInstance();
                $statusDir = $handler->getStatusDir();

                if ($this->buildTimeStamp != null) {
                    $fullStatusDir = Xinc_Build_History::getBuildDir(
                        $this->project, $this->buildTimeStamp
                    );
                } else {
                    $fullStatusDir = Xinc_Build_History::getLastBuildDir($this->project);
                    $this->buildTimeStamp = Xinc_Build_History::getLastBuildTime($this->project);
                }
                //$statusFile = $fullStatusDir . DIRECTORY_SEPARATOR . 'build.ser';
                $this->build = Xinc_Build::unserialize(
                    $this->project, 
                    $this->buildTimeStamp,
                    Xinc_Gui_Handler::getInstance()->getStatusDir()
                );
                $timezone = $this->build->getConfigDirective('timezone');
                if ($timezone !== null) {
                    Xinc_Timezone::set($timezone);
                }

                $detailDir = $fullStatusDir;

                /**
                    * get History Builds
                    */
                //$this->historyBuilds = $this->getHistoryBuilds($statusDir);

                /**
                    * Generate the build selector on the right
                    */
                $this->_generateBuildsView();
                /**
                    * Overview info tab
                    */
                $this->_generateSummaryView();
                /**
                    * Generate the tab for the log messages
                    */
                $this->_generateLogView();
                /**
                    * Generate the external tabs that were registered through a hook
                    */
                $this->_generateExternalExtensions();

                include Xinc_Data_Repository::getInstance()->getWeb(
                    'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
                    . 'detail' . DIRECTORY_SEPARATOR . 'projectDetail.phtml'
                );
                break;
            default:
                break;
        }
        /**
         * restore to system timezone
         */
        $xincTimezone = Xinc_Gui_Handler::getInstance()->getConfigDirective('timezone');
        if ($xincTimezone !== null) {
            Xinc_Timezone::set($xincTimezone);
        } else {
            Xinc_Timezone::reset();
        }
    }

    public function getPaths()
    {
        return array('/dashboard/detail', '/dashboard/detail/');
    }

    private function _registerExtension($extensionPoint, $detail)
    {
        if (!isset($this->_internalExtensions[$extensionPoint])) {
            $this->_internalExtensions[$extensionPoint] = array();
        }
        $this->_internalExtensions[$extensionPoint][] = $detail;
    }

    public function getExtensionPoints()
    {
        return array('BUILD_DETAILS', 'BUILD_SUMMARY');
    }
}
