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
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Plugin/Repos/Gui/Menu/Extension/Item.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Projects/Menu.php';
require_once 'Xinc/Data/Repository.php';
require_once 'Xinc/Build/Repository.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;

    private $_extensions = array();

    public $projects = array();

    public $menu;

    public $builds;

    public $features;

    public $projectMenuItem;

    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->_plugin = $plugin;
        $this->builds = new Xinc_Build_Iterator();
    }

    public function handleEvent($eventId)
    {
        switch ($eventId) {
            case Xinc_Gui_Event::PAGE_LOAD: 
                    $query = urldecode($_SERVER['REQUEST_URI']);
                    $this->features = $this->_extensions['PROJECT_FEATURE'];

                    $handler = Xinc_Gui_Handler::getInstance();
                    $statusDir = $handler->getStatusDir();
                    $dir = opendir($statusDir);
                    while ($file = readdir($dir)) {
                        $project = array();
                        $fullfile = $statusDir . DIRECTORY_SEPARATOR . $file;

                        if (!in_array($file, array('.', '..')) && is_dir($fullfile)) {
                            $project['name']=$file;
                            $statusfile = $fullfile . DIRECTORY_SEPARATOR . 'build.ser';
                            //$xincProject = $fullfile . DIRECTORY_SEPARATOR . '.xinc';
                            if (file_exists($statusfile)) {
                                //$ini = parse_ini_file($statusfile, true);
                                $project = new Xinc_Project();
                                $project->setName($file);
                                try {
                                    $object = Xinc_Build_Repository::getLastBuild($project);
                                    $this->builds->add($object);
                                } catch (Exception $e) {
                                }
                            } else if (file_exists($xincProject)) {
                                $project['build.status'] = -10;
                                $project['build.time'] = 0;
                                $project['build.label'] = '';
                                $this->projects[]=$project;
                            }
                            $this->menu = '';
                            if (isset($this->_extensions['MAIN_MENU'])) {
                                if (is_array($this->_extensions['MAIN_MENU'])) {
                                    foreach ($this->_extensions['MAIN_MENU'] as $extension) {
                                       $this->menu .= call_user_func_array($extension, array($this, 'Dashboard'));
                                    }
                                }
                            }
                        }
                    }
                    if (preg_match('/\/dashboard\/projects.*/', $query)) {
                        include_once Xinc_Data_Repository::getInstance()->get('templates' . DIRECTORY_SEPARATOR
                                                                             . 'dashboard' . DIRECTORY_SEPARATOR
                                                                             . 'projects.phtml');
                    }
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
        return array('/dashboard', '/dashboard/');
    }

    public function init()
    {
        $menuWidget = Xinc_Gui_Widget_Repository::getInstance()->
                                                  getWidgetByClassName('Xinc_Plugin_Repos_Gui_Menu_Widget');

        $menuWidget->registerExtension('MAIN_MENU_ITEMS', $this->generateDashboardMenuItem());

        $menuWidget->registerExtension('MAIN_MENU_ITEMS', $this->generateProjectsMenuItem());
    }

    public function generateDashboardMenuItem()
    {
        $menuItem = new Xinc_Plugin_Repos_Gui_Menu_Extension_Item(
            'widget-dashboard',
            'Dashboard', 
            './dashboard/projects',
            'Dashboard',
            'icon-dashboard'
        );
        return $menuItem;
    }

    public function generateProjectsMenuItem()
    {
        if (isset($this->_extensions['PROJECT_MENU_ITEM'])) {
            $this->projectMenuItem = new Xinc_Plugin_Repos_Gui_Dashboard_Projects_Menu(
                'projects',
                'Projects',
                '',
                'Projects'
            );
            foreach ($this->_extensions['PROJECT_MENU_ITEM'] as $extension) {
                $this->projectMenuItem->registerSubExtension($extension);
            }
        } else {
            $this->projectMenuItem = new Xinc_Plugin_Repos_Gui_Dashboard_Projects_Menu(
                'projects',
                'Projects',
                '',
                'Projects',
                '',
                true,
                false
            );
        }

        return $this->projectMenuItem;
    }

    public function registerExtension($extension, $ext)
    {
        if ($extension == 'PROJECT_MENU_ITEM' && $this->projectMenuItem !== null) {
            $this->projectMenuItem->registerSubExtension($ext);
            
        } else {
            if (!isset($this->_extensions[$extension])) {
                $this->_extensions[$extension] = array();
            }
            $this->_extensions[$extension][] = $ext;
        }
    }

    public function getExtensionPoints()
    {
        return array('PROJECT_MENU_ITEM', 'PROJECT_LAST_BUILD_ENTRY');
    }

    public function hasExceptionHandler()
    {
        return false;
    }

    public function handleException(Exception $e)
    {
    }
}