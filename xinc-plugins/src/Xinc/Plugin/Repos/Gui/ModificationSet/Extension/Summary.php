<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.ModificationSet.Extension
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

require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';
require_once 'Xinc/Data/Repository.php';

class Xinc_Plugin_Repos_Gui_ModificationSet_Extension_Summary
    extends Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{
    public function getTitle()
    {
        return 'Modification Summary';
    }

    public function getContent(Xinc_Build_Interface $build)
    {
        $changeSet = $build->getProperties()->get('changeset');
        if ($changeSet instanceof Xinc_Plugin_Repos_ModificationSet_Result ) {
            if (!$changeSet->isChanged()) {
                return false;
            }

            $templateFile = Xinc_Data_Repository::getInstance()->getWeb(
                'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
                . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
                . 'modifications.phtml'
            );
            $templateContent = file_get_contents($templateFile);
            $templateContent = str_replace(array('{previous_revision}',
                                                 '{current_revision}',
                                                 '{files_modified}',
                                                 '{files_added}',
                                                 '{files_deleted}',
                                                 '{files_merged}',
                                                 '{files_conflicted}'),
                                           array($changeSet->getLocalRevision(),
                                                 $changeSet->getRemoteRevision(),
                                                 count($changeSet->getUpdatedResources()),
                                                 count($changeSet->getNewResources()),
                                                 count($changeSet->getDeletedResources()),
                                                 count($changeSet->getMergedResources()),
                                                 count($changeSet->getConflictResources())),
                                           $templateContent);
            return $templateContent;
        } else {
            return false;
        }
    }

    public function getExtensionPoint()
    {
        return 'BUILD_DETAILS';
    }
}