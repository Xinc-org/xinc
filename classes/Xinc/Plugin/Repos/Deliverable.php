<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos
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

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Publisher/Deliverable/Task.php';
require_once 'Xinc/Plugin/Repos/Gui/Deliverable/Widget.php';
require_once 'Xinc/Plugin/Repos/Api/Deliverable.php';

class Xinc_Plugin_Repos_Deliverable extends Xinc_Plugin_Base
{
    const DELIVERABLE_DIR = 'deliverable';

    public function validate()
    {
        return true;
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Publisher_Deliverable_Task($this));
    }

    /**
     *
     * @return array of Gui Widgets
     */
    public function getGuiWidgets()
    {
        return array(new Xinc_Plugin_Repos_Gui_Deliverable_Widget($this));
    }

    public function getApiModules()
    {
        return array();
    }

    public function getDeliverableDir(Xinc_Build_Interface $build)
    {
        $statusDir = Xinc::getInstance()->getStatusDir();
        $subDir = $build->getStatusSubDir();
        $fullDir = $statusDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . self::DELIVERABLE_DIR;

        return $fullDir;
    }

    /**
     * Copies a file into a special artifacts directory for the build
     *
     * @param Xinc_Build_Interface $build
     * @param string $sourceFile
     *
     * @return boolean
     */
    public function registerDeliverable(
        Xinc_Build_Interface $build, $sourceFile, $alias = null
    ) {
        $build->debug('Trying to register deliverable: ' . $sourceFile);
        $sourceFile = realpath($sourceFile);
        $alias = basename($alias);
        $statusDir = Xinc::getInstance()->getStatusDir();
        
        $projectDir = Xinc::getInstance()->getProjectDir();
        
        
        $subDir = $build->getStatusSubDir();
        $fullDir = self::getDeliverableDir($build);
        $targetFile = $fullDir . DIRECTORY_SEPARATOR . basename($sourceFile);
        
        /**
         * Verify that the source is in the projectdir
         */
        $relativePath = str_replace($projectDir, '', $sourceFile);
        if ($relativePath == $sourceFile) {
            /**
             * the filename was not within the project path,
             * we need to prevent this file from being copied.
             * 
             * Future: run Xinc in a chroot environment per project
             */
            $build->error('Registering deliverable: ' . $sourceFile . '->' . $targetFile . ' failed.');
            $build->error('-- ' . $sourceFile . ' is not within project dir. Security Problem.');
            return false;
        }
        
        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        if (is_dir($sourceFile)) {
            $res = false;
            $status = 'FAILURE';
            $build->info('Registering deliverable: ' . $sourceFile
                        . ' failed, cannot register a directory as a deliverable');
        } else {
            $res = copy($sourceFile, $targetFile);
            if ($res) {
                
                chmod($targetFile, 0755);
                $status = 'OK';
                $deliverables = $build->getInternalProperties()->get('deliverables');
                if (!is_array($deliverables)) {
                    $deliverables = array(array('deliverables'=>array()),
                                          array('aliases'=>array()));
                }
                $deliverableFilename = basename($targetFile);
                if (!isset($deliverables['deliverables'])) {
                    $deliverables['deliverables'] = array();
                }
                if (!isset($deliverables['aliases'])) {
                    $deliverables['aliases'] = array();
                }
                $deliverables['deliverables'][$deliverableFilename] = $targetFile;
                if ($alias != null) {
                    if (!isset($deliverables['aliases'])) {
                        $deliverables['aliases'] = array();
                    }
                    $deliverables['aliases'][$alias] = $deliverableFilename;
                }
                $build->getInternalProperties()->set('deliverables', $deliverables);
            } else {
                $status = 'FAILURE';
            }
        }

        $build->info('Registering deliverable: ' . $sourceFile . '->' . $targetFile . ', result: ' . $status);
        return $res;
    }
}
