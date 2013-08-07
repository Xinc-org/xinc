<?php
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

class Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension_Log
    extends Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{
    public function getTitle()
    {
        return 'Log Message';
    }

    public function getContent(Xinc_Build_Interface $build)
    {
        $logTemplateFile = Xinc_Data_Repository::getInstance()->getWeb(
            'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
            . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
            . 'logJs.phtml'
        );
        $logTemplate = file_get_contents($logTemplateFile);

        $content = str_replace(array('{projectname}','{buildtime}'),
                               array($build->getProject()->getName(), $build->getBuildTime()),
                               $logTemplate);

        return $content;
    }
}