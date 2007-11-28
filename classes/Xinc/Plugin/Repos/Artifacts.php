<?php
/**
 * Artifacts Plugin - allows to register artifacts for a build
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Gui/Artifacts/Widget.php';
require_once 'Xinc/Plugin/Repos/Publisher/Artifacts/Task.php';
class Xinc_Plugin_Repos_Artifacts  extends Xinc_Plugin_Base
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
    
    public function getArtifactsDir(Xinc_Build_Interface &$build)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $subDir = $build->getStatusSubDir();
        $fullDir = $statusDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . self::ARTIFACTS_DIR;
        
        return $fullDir;
    }
    /**
     * Copies a file into a special artifacts directory for the build
     *
     * @param Xinc_Build_Interface $build
     * @param string $sourceFile
     * @return boolean
     */
    public function registerArtifact(Xinc_Build_Interface &$build, $sourceFile)
    {
        $statusDir = Xinc::getInstance()->getStatusDir();
        $subDir = $build->getStatusSubDir();
        $fullDir = $statusDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . self::ARTIFACTS_DIR;
        $targetFile = $fullDir . DIRECTORY_SEPARATOR . basename($sourceFile);
        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        $res = copy($sourceFile, $targetFile);
        if ($res) {
            $status = 'OK';
        } else {
            $status = 'FAILURE';
        }
        $build->info('Registering artifact: ' . $sourceFile . '->' . $targetFile . ', result: ' . $status);
        return $res;
    }
}
