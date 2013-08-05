<?php
/**
 * Xinc - Continuous Integration.
 * Artifacts Plugin - allows to register artifacts for a build
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

require_once 'Xinc/Plugin/Abstract.php';
require_once 'Xinc/Plugin/Repos/Gui/Artifacts/Widget.php';
require_once 'Xinc/Plugin/Repos/Publisher/Artifacts/Task.php';

class Xinc_Plugin_Repos_Artifacts extends Xinc_Plugin_Abstract
{
    const ARTIFACTS_DIR = 'artifacts';

    public function validate()
    {
        return true;
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Publisher_Artifacts_Task($this));
    }

    /**
     *
     * @return array of Gui Widgets
     */
    public function getGuiWidgets()
    {
        return array(new Xinc_Plugin_Repos_Gui_Artifacts_Widget($this));
    }

    public function getArtifactsDir(Xinc_Build_Interface $build)
    {
        $statusDir = Xinc::getInstance()->getStatusDir();
        $subDir = $build->getStatusSubDir();
        $fullDir = $statusDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . self::ARTIFACTS_DIR;

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
    public function registerArtifact(Xinc_Build_Interface $build, $sourceFile)
    {
        $sourceFile = realpath($sourceFile);

        $statusDir = Xinc::getInstance()->getStatusDir();
        $projectDir = Xinc::getInstance()->getProjectDir();
        $subDir = $build->getStatusSubDir();
        $fullDir = self::getArtifactsDir($build);
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
            $build->error('Registering artifact: ' . $sourceFile . '->' . $targetFile . ' failed.');
            $build->error('-- ' . $sourceFile . ' is not within project dir. Security Problem.');
            return false;
        }

        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        if (is_dir($sourceFile)) {
            if (DIRECTORY_SEPARATOR == '\\') {
                exec('xcopy /E /Y /I ' . $sourceFile . ' ' . $targetFile, $out, $res);
                //chmod($targetFile, 0755);
            } else {
                exec('cp -Rf ' . $sourceFile . ' ' . $targetFile, $out, $res);
            }
            if ($res==0) {
                $status = 'OK';
            } else {
                $status = 'FAILURE';
            }
        } else {
            $res = copy($sourceFile, $targetFile);
            if ($res) {
                chmod($targetFile, 0755);
                $status = 'OK';
            } else {
                $status = 'FAILURE';
            }
        }
        $build->info('Registering artifact: ' . $sourceFile . '->' . $targetFile . ', result: ' . $status);
        return $res;
    }
}
