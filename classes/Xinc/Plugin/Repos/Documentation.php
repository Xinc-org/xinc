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
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Publisher/Documentation/Task.php';
require_once 'Xinc/Plugin/Repos/Gui/Documentation/Widget.php';

class Xinc_Plugin_Repos_Documentation extends Xinc_Plugin_Base
{
    const DOCUMENTATION_DIR = 'documentation';

    public function validate()
    {
        return true;
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Publisher_Documentation_Task($this));
    }

    /**
     *
     * @return array of Gui Widgets
     */
    public function getGuiWidgets()
    {
        return array(new Xinc_Plugin_Repos_Gui_Documentation_Widget($this));
    }

    public function getApiModules()
    {
        return array();
    }

    public function getDocumentationDir(Xinc_Build_Interface &$build)
    {
        $statusDir = Xinc::getInstance()->getStatusDir();
        $subDir = $build->getStatusSubDir();
        $fullDir = $statusDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . self::DOCUMENTATION_DIR;
        
        return $fullDir;
    }

    /**
     * Copies a file into a special artifacts directory for the build
     *
     * @param Xinc_Build_Interface $build
     * @param string $sourceFile
     * @return boolean
     */
    public function registerDocumentation(Xinc_Build_Interface &$build, $sourceFile, $alias, $index)
    {
        $build->debug('Trying to register documentation: ' . $sourceFile);
        $sourceFile = realpath($sourceFile);
        $alias = basename($alias);
        $statusDir = Xinc::getInstance()->getStatusDir();
        
        $projectDir = Xinc::getInstance()->getProjectDir();
        
        $sourceFile = preg_replace('/\/+/', '/', $sourceFile);
        $index = preg_replace('/\/+/', '/', $index);
        
        
        $relativeIndex = str_replace($sourceFile, '', $index);
        
        $subDir = $build->getStatusSubDir();
        $fullDir = self::getDocumentationDir($build);
        $targetDir = $fullDir . DIRECTORY_SEPARATOR . basename($alias);
        $targetFile = $targetDir . DIRECTORY_SEPARATOR . basename($sourceFile);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

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
            $build->error('Registering doc: ' . $sourceFile . '->' . $targetFile . ' failed.');
            $build->error('-- ' . $sourceFile . ' is not within project dir. Security Problem.');
            return false;
        }

        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        if (is_dir($sourceFile)) {
            $relativePath = str_replace($sourceFile, '', $index);
            if ($relativePath == $index) {
                /**
                 * the index file was not within the doc path,
                 * we need to prevent this file from being copied.
                 * 
                 * Future: run Xinc in a chroot environment per project
                 */
                $build->error('Registering doc: ' . $sourceFile . '->' . $targetFile . ' failed.');
                $build->error('-- ' . $index . ' is not within ' . $sourceFile . ' dir. Security Problem.');
                return false;
            }
            if (DIRECTORY_SEPARATOR == '\\') {
                exec('xcopy /E /Y /I ' . str_replace(' ','\ ',$sourceFile) . '\*" "' . $targetDir . '"', $out, $res1);
            } else {
                exec('cp  -Rf ' . str_replace(' ','\ ',$sourceFile) . '/* "' . $targetDir . '"', $out, $res1);
            }
            $res = false;
            if ($res1==0) {
                $status = 'OK';
                $res = true;
            } else {
                $status = 'FAILURE';
                $res = false;
            }
            $targetIndexFile = $targetDir . DIRECTORY_SEPARATOR . $relativeIndex;
            $registerFile = $targetDir;
        } else {
            $res = copy($sourceFile, $targetFile);
            $targetIndexFile = $targetFile;
            $registerFile = $targetFile;
        }
            if ($res) {
                chmod($targetDir, 0755);
                $status = 'OK';
                $docs = $build->getInternalProperties()->get('documentation');
                if (!is_array($docs)) {
                    $docs = array();
                }
                $docsDir = dirname($targetFile);

                $docs[$alias] = array('file'=>$registerFile, 'index'=>$targetIndexFile);
                $build->getInternalProperties()->set('documentation', $docs);
            } else {
                $status = 'FAILURE';
            }

        $build->info('Registering documentation: ' . $sourceFile . '->' . $targetFile . ', result: ' . $status);
        return $res;
    }
}