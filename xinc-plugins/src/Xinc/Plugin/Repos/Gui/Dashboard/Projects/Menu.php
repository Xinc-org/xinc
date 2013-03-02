<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Menu Widget which lists all Projects
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Dashboard.Projects
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
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Build/Repository.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Projects_Menu extends Xinc_Plugin_Repos_Gui_Menu_Extension_Item
{
    private $_subMenus = array();

    public function registerSubExtension($extension)
    {
        if (!in_array($extension, $this->_subMenus)) {
            $this->_subMenus[] = $extension;
        }
        
    }

    protected function _generateChildren()
    {
        $builds = new Xinc_Build_Iterator();

        $handler = Xinc_Gui_Handler::getInstance();
        $statusDir = $handler->getStatusDir();
        $dir = opendir($statusDir);
        while ($file = readdir($dir)) {
            $fullfile = $statusDir . DIRECTORY_SEPARATOR . $file;

            if (!in_array($file, array('.', '..')) && is_dir($fullfile)) {
                $statusfile = $fullfile . DIRECTORY_SEPARATOR . 'build.ser';

                if (file_exists($statusfile)) {
                    $project = new Xinc_Project();
                    $project->setName($file);
                    try {
                        $object = Xinc_Build_Repository::getLastBuild($project);
                        $builds->add($object);
                    } catch (Exception $e) {
                    }
                }
            }
        }
        $projects = array();
        while ($builds->hasNext()) {
            $build = $builds->next();

            /**
             * Do we have children?
             */
            $children = array();
            foreach ($this->_subMenus as $subExtension) {
                $item = $subExtension->getItem($build->getProject());
                if (!$item instanceof Xinc_Plugin_Repos_Gui_Menu_Extension_Item) {
                    continue;
                }
                $children[] = $item->generate();
            }

            $item = new Xinc_Plugin_Repos_Gui_Menu_Extension_Item(
                'project-' . $build->getProject()->getName() . '-' . $build->getBuildTime(),
                $build->getLabel() . ' - ' . $build->getProject()->getName(),
                './dashboard/detail?project=' . $build->getProject()->getName()
                . '&timestamp=' . $build->getBuildTime(),
                $build->getProject()->getName(),
                '',
                false,
                count($children)>0 ? false: true,
                $children
            );

            $projects[] = $item->generate();
        }
        return implode(',', $projects);
    }
}