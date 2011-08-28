<?php
declare(encoding = 'utf-8');
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

class Xinc_Plugin_Repos_Gui_ModificationSet_Extension_ChangeLog
    extends Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{

    public function getTitle()
    {
        return 'Change Log';
    }

    public function getContent(Xinc_Build_Interface &$build)
    {
        $changeSet = $build->getProperties()->get('changeset');
        if ($changeSet instanceof Xinc_Plugin_Repos_ModificationSet_Result ) {
            $logMessageTemplateFile = Xinc_Data_Repository::getInstance()->get(
                'templates' . DIRECTORY_SEPARATOR  . 'dashboard' . DIRECTORY_SEPARATOR
                . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
                . 'modification_log_row.phtml'
            );
            $logMessageTemplateContent = file_get_contents($logMessageTemplateFile);
            $logMessagesArr = array();
            $logMessages = $changeSet->getLogMessages();

            if (count($logMessages) == 0) {
                return false;
            }
            foreach ($logMessages as $log) {
                $xpandable = '';
                $logMessageString = $log['message'];
                $newLogString = '';
                for ($i=0; $i < strlen($logMessageString); $i = $i + 60) {
                    $newLogString .= substr($logMessageString, $i, 60) . "<br/>";
                }
                $logMessageString = $newLogString;
                if (strlen($logMessageString)>80) {
                    $xpandable = 'expandable';
                    $logMessageDiv = '<div class="mdesc">
                        <div class="short">'. substr($logMessageString, 0, 60) .' ...</div>
            <div class="long">';
                    $logMessageDiv .= $logMessageString;
                $logMessageDiv .='</div>
                        </div>';
                } else {
                    $logMessageDiv = '<div class="mdesc">';
                    $logMessageDiv .= $logMessageString;
                    $logMessageDiv .= '</div>';
                }
                $logContent = str_replace(array('{author}',
                                                '{revision}',
                                                '{message}',
                                                '{expandable}'),
                                          array($log['author'],
                                                $log['revision'],
                                                $logMessageDiv,
                                                $xpandable),
                                           $logMessageTemplateContent);
                $logMessagesArr[] = $logContent;
            }
            $templateFile = Xinc_Data_Repository::getInstance()->get(
                'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
                . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
                . 'modification_log.phtml'
            );
            $templateContent = file_get_contents($templateFile);
            $templateContent = str_replace(array('{logmessages}'),
                                           array(implode('', $logMessagesArr)),
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