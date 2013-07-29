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

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';
require_once 'Xinc/Plugin/Repos/Gui/Statistics/Menu/Item.php';
require_once 'Xinc/Data/Repository.php';
require_once 'Xinc/Build/History.php';
require_once 'Xinc/Build/Repository.php';

class Xinc_Plugin_Repos_Gui_Statistics_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;

    private $extensions = array();

    public $scripts = '';

    private $projectName;

    private $tmpDir = '/tmp/';

    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->_plugin = $plugin;
        try {
            $this->tmpDir = Xinc_Ini::getInstance()->get('tmp_dir', 'xinc');
        } catch (Exception $e) {
            $this->tmpDir = '/tmp/';
        }
    }

    public function handleEvent($eventId)
    {
        if (isset($_REQUEST['project'])) {
            $this->projectName = $_REQUEST['project'];
        }

        // TODO Cleanup this
        $path = $_SERVER['REDIRECT_URL'];
        $path = preg_replace('#^' . dirname($_SERVER['PHP_SELF']) . '#', '', $path);
        $path = '/' . ltrim($path, '/');

        switch($path) {
            case '/statistics/graph/':
                $graphName = $_REQUEST['name'];
                header('Content-Type: image/svg+xml');
                header('Content-Disposition: inline; filename=' . $graphName);
                $content = $this->loadGraph($graphName);
                header('Content-Length: ' . strlen($content));
                echo $content;
                break;
            case '/statistics/':
            default:
                ob_start();
                include Xinc_Data_Repository::getInstance()->get(
                    'templates' . DIRECTORY_SEPARATOR
                    . 'statistics' . DIRECTORY_SEPARATOR
                    . 'graphbase.phtml'
                );
                ob_end_flush();
                break;
        }
    }

    protected function loadGraph($name)
    {
        $dir = $this->tmpDir . DIRECTORY_SEPARATOR;
        $name = basename($name);
        $fileName = 'graph_' . $this->projectName . '_' . $name;
        $file = $dir . $fileName;
        if (file_exists($file) && realpath($file) == $file) {
            return file_get_contents($file);
        } else {
            return null;
        }
    }

    public function getGraphFileName($name)
    {
        $dir = $this->tmpDir . DIRECTORY_SEPARATOR;
        $fileName = basename($name);
        $fileName = 'graph_' . $this->projectName . '_' . $fileName;
        $fileName = $dir . $fileName;

        return $fileName;
    }

    public function getPaths()
    {
        return array('/statistics', '/statistics/');
    }

    public function getGraphs()
    {
        $project = new Xinc_Project();

        $project->setName($this->projectName);
        $contents = array();

        $lastBuildTime = Xinc_Build_History::getLastBuildTime($project);

        $cacheFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'xinc_statistics_' . $this->projectName;

        if (file_exists($cacheFile) && filemtime($cacheFile) == $lastBuildTime) {
            // we have a cached version
            header('Xinc-Cache: ' . $lastBuildTime);
            return readfile($cacheFile);
        } else {
            try {
                $baseBuildData = array();
                if (file_exists($cacheFile)) {
                    $cachedLastBuildTime = filemtime($cacheFile);
                    $historyBuilds = $this->getHistoryBuildsByTimestamp($project, $cachedLastBuildTime + 1);
                } else {
                    $historyBuilds = $this->getHistoryBuilds($project, 0);
                }
            } catch (Exception $e1) {
                $historyBuilds = array();
            }
            if (isset($this->extensions['STATISTIC_GRAPH'])) {
                $i = 0;
                foreach ($this->extensions['STATISTIC_GRAPH'] as $extension) {
                    if ($extension instanceof Xinc_Plugin_Repos_Gui_Statistics_Graph) {
                        $baseBuildData = $this->loadGraphData($project, $extension->getId());
                        if (!is_array($baseBuildData)) {
                            $baseBuildData = array();
                        }
                        $data = $extension->buildDataSet($project, $historyBuilds, $baseBuildData);
                        $this->storeGraphData($project, $extension->getId(), $data);
                        $contents[] = $extension->generate($data, array('#1c4a7e','#bb5b3d'));
                        $i++;
                        if ($i % 2 == 0) {
                            $contents[] = '<br/>';
                        }
                    }
                }
            }
            $contents = implode("\n", $contents);
            file_put_contents($cacheFile, $contents);
            touch($cacheFile, $lastBuildTime);
        }
        return $contents;
    }

    private function storeGraphData(Xinc_Project $project, $id, $data)
    {
        //$fileName = $this->getGraphFileName($id);
        $fileName = $this->tmpDir . DIRECTORY_SEPARATOR . 'graph_data_' . $project->getName() . '_' . $id . '.ser';
        file_put_contents($fileName, serialize($data));
    }

    private function loadGraphData(Xinc_Project $project, $id)
    {
        $fileName = $this->tmpDir . DIRECTORY_SEPARATOR . 'graph_data_' . $project->getName() . '_' . $id . '.ser';
        //$fileName = $this->getGraphFileName($id);
        $data = @unserialize(file_get_contents($fileName));
        return $data;
    }

    public function getProjectName()
    {
        return $this->projectName;
    }

    private function getHistoryBuilds(Xinc_Project $project, $start, $limit = null)
    {
        $buildHistoryArr = Xinc_Build_History::getFromTo($project, $start, $limit, false);

        return $buildHistoryArr;
    }

    private function getHistoryBuildsByTimestamp(Xinc_Project $project, $timestamp, $limit = null)
    {
        $buildHistoryArr = Xinc_Build_History::getFromToTimestamp($project, $timestamp, $limit, false);

        return $buildHistoryArr;
    }

    public function init()
    {
        $indexWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Widget');
        $extension = new Xinc_Plugin_Repos_Gui_Statistics_Menu_Item($this);

        $indexWidget->registerExtension('PROJECT_MENU_ITEM', $extension);
    }

    public function generateStatisticsMenu(Xinc_Project $project)
    {
        $numberOfGraphs = count($this->extensions['STATISTIC_GRAPH']);
        $graphHeight = 350;
        $statisticsMenu = new Xinc_Plugin_Repos_Gui_Menu_Extension_Item(
            'statistics-' . $project->getName(),
            'Statistics',
            './statistics/?project=' . $project->getName(),
            'Statistics - ' . $project->getName(),
            '',
            true,
            false
        );
        return $statisticsMenu;
    }

    public function registerExtension($extensionPoint, $extension)
    {
        if (!isset($this->extensions[$extensionPoint])) {
            $this->extensions[$extensionPoint] = array();
        }
        switch ($extensionPoint) {
            case 'STATISTIC_GRAPH':
                if ($extension instanceof Xinc_Plugin_Repos_Gui_Statistics_Graph) {
                    $extension->setWidget($this);
                    $this->extensions[$extensionPoint][] = $extension;
                }
                break;
        }
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function getExtensionPoints()
    {
        return array('STATISTIC_GRAPH');
    }

    public function hasExceptionHandler()
    {
        return false;
    }

    public function handleException(Exception $e)
    {
    }
}
