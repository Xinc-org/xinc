<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Deliverable.Extension
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

require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Extension/ProjectInfo.php';

class Xinc_Plugin_Repos_Gui_Deliverable_Extension_Last
    extends Xinc_Plugin_Repos_Gui_Dashboard_Extension_ProjectInfo
{
    public function getTitle()
    {
        return 'Deliverables';
    }

    public function getContent(Xinc_Build_Interface $build)
    {
        $deliverableLinkTemplateFile = Xinc_Data_Repository::getInstance()->get(
            'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
            . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
            . 'deliverable-link.phtml'
        );
        $deliverableLinkTemplate = file_get_contents($deliverableLinkTemplateFile);
        $getDeliverableUrl = './api/deliverable/get/download/' 
                           . $build->getProject()->getName()
                           . '/' . $build->getBuildTime() . '/';
        $deliverableLinks = array();
        $deliverables = $build->getInternalProperties()->get('deliverables');
        if (!isset($deliverables['deliverables'])) {
            return false;
        }

        foreach ($deliverables['deliverables'] as $fileName => $fileLocation) {
            $publicName = $fileName;
            foreach ($deliverables['aliases'] as $alias => $realName) {
                if ($realName == $publicName) {
                    $publicName = $alias;
                    break;
                }
            }
            $link = $getDeliverableUrl . $publicName;
            $deliverableLinks[] = call_user_func_array('sprintf', array($deliverableLinkTemplate,
                                                                            $link, $publicName));
        }

        return implode(', ', $deliverableLinks);
    }

    public function getExtensionPoint()
    {
        return 'BUILD_SUMMARY';
    }
}