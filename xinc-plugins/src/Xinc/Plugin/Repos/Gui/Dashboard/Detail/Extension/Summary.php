<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * LogDetail Widget Extension, registers the logdetails view in the project details
 * tab
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Dashboard.Detail.Extension
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

require_once 'Xinc/Gui/Widget/Extension/Interface.php';
require_once 'Xinc/Data/Repository.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';
require_once 'Xinc/Data/Repository.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension_Summary
    extends Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{
    private $_extensions = array();

    public function getTitle()
    {
        return 'Summary';
    }

    public function getContent(Xinc_Build_Interface $build)
    {
        switch ($build->getStatus()) {
            case 1:
                $image = './images/passed.png';
                break;
            case -1:
                $image = './images/stopped.png';
                break;
            case 0:
                $image = './images/failed.png';
                break;
            default:
                $image = './images/stopped.png';
                break;
        }

        $overviewTemplateFile = Xinc_Data_Repository::getInstance()->get(
            'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
            . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
            . 'overview.phtml'
        );
        $overviewTemplate = file_get_contents($overviewTemplateFile);

        $content = call_user_func_array('sprintf', array($overviewTemplate,
                                                         $image,
                                                         date('Y-m-d H:i:s', $build->getBuildTime())
                                                         . '-' . Xinc_Timezone::get(),
                                                         $build->getLabel(),
                                                         $this->_generateAllExtensions($build)));

        return $content;
    }

    public function registerDetailExtension(
        Xinc_Plugin_Repos_Gui_Dashboard_Extension_ProjectInfo &$extension
    ) {
        $this->_extensions[] = $extension;
    }

    protected function _generateAllExtensions(Xinc_Build_Interface $build)
    {
        $overviewTemplateFile = Xinc_Data_Repository::getInstance()->get(
            'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
            . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
            . 'overview-extension.phtml'
        );
        $overviewTemplate = file_get_contents($overviewTemplateFile);
        $contentParts = array();
        foreach ($this->_extensions as $ext) {
            $extContent = $ext->getContent($build);
            if ($extContent === false) continue;
            $content = call_user_func_array('sprintf', array($overviewTemplate,
                                                         $ext->getTitle(), $extContent));
            $contentParts[] = $content;
        }

        return implode("\n",$contentParts);
    }
}