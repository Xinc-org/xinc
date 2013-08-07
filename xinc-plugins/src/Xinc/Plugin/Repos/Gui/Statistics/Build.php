<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Statistics
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
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Build/History.php';
require_once 'Xinc/Build/Repository.php';
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Extension/Prominent.php';
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph/BuildDuration.php';
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph/BuildStatus.php';
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph/PhpUnitTestResults.php';

class Xinc_Plugin_Repos_Gui_Statistics_Build extends Xinc_Gui_Widget_Abstract
{
    public $scripts = '';


    public function getPhpUnitGraph()
    {
        $graph = new Xinc_Plugin_Repos_Gui_Statistics_Graph_PhpUnitTestResults(
            'PHPUnit Tests', 'line', '#f2f2f2', 'blue'
        );
        return $graph;
    }

    public function getBuildStatusGraph()
    {
        $graph = new Xinc_Plugin_Repos_Gui_Statistics_Graph_BuildStatus(
            'Build Status', 'pie', '#f2f2f2', 'blue'
        );
        return $graph;
    }

    public function getBuildDurationGraph()
    {
        $graph = new Xinc_Plugin_Repos_Gui_Statistics_Graph_BuildDuration(
            'Build Duration in seconds', 'line', '#f2f2f2', 'blue'
        );
        return $graph;
    }

    private function _getHistoryBuilds(Xinc_Project $project, $start, $limit=null)
    {
        /**$statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $project->getName() . '.history';

        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $totalCount = count($buildHistoryArr);
        if ($limit==null) {
            $limit = $totalCount;
        }
        /**
         * turn it upside down so the latest builds appear first
         */
        /**
        $buildHistoryArr = array_reverse($buildHistoryArr, true);
        $buildHistoryArr = array_slice($buildHistoryArr, $start, $limit, true);*/

        $buildHistoryArr = Xinc_Build_History::getFromTo($project, $start, $limit);
        $totalCount = Xinc_Build_History::getCount($project);

        $builds = array();

        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                //$buildObject = Xinc_Build::unserialize($project,
                //                                       $buildTimestamp,
                //                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                $buildObject = Xinc_Build_Repository::getBuild($project, $buildTimestamp);
                $builds[] = array('number'=>$buildObject->getNumber(),
                                  'y'=>$buildObject->getStatistics()->get('build.duration'),
                                  'xlabel'=>$buildObject->getNumber());
            } catch (Exception $e) {
                // TODO: Handle
            }
        }

        $builds = array_reverse($builds, true);

        return $builds;
    }

    public function init()
    {
        $statisticWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Statistics_Widget');

        $statisticWidget->registerExtension('STATISTIC_GRAPH', $this->getBuildStatusGraph());
        $statisticWidget->registerExtension('STATISTIC_GRAPH', $this->getBuildDurationGraph());
        $statisticWidget->registerExtension('STATISTIC_GRAPH', $this->getPhpUnitGraph());

        $detailWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Detail');

        $extension = new Xinc_Plugin_Repos_Gui_Statistics_Extension_Prominent();
        $extension->setWidget($this);

        $detailWidget->registerExtension('BUILD_SUMMARY', $extension);

        $dashboardWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Widget');

        $dashboardWidget->registerExtension('PROJECT_FEATURE', $extension);
    }

    public function registerExtension($extensionPoint, $extension)
    {
    }
}
