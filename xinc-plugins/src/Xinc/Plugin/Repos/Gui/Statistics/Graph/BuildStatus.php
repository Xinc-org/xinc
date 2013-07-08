<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Statistics.Graph
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

require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph.php';

class Xinc_Plugin_Repos_Gui_Statistics_Graph_BuildStatus extends Xinc_Plugin_Repos_Gui_Statistics_Graph
{
    public function getColorScheme()
    {
        return array('#00ff00','#ff0000');
    }

    public function buildDataSet(
        Xinc_Project $project, array $buildHistoryArr = array(), $previousData = array()
    ) {
        if (count($previousData)>0) {
            $data = $previousData;
        } else {
            $data = array('Build Status'=>array('Successful Builds'=>0, 'Failed Builds'=>0));
        }

        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize(
                    $project,
                    $buildTimestamp,
                    Xinc_Gui_Handler::getInstance()->getStatusDir()
                );
                if ($buildObject->getStatus() == Xinc_Build_Interface::PASSED) {
                    $data['Build Status']['Successful Builds']++;
                } else {
                    $data['Build Status']['Failed Builds']++;
                }
                unset($buildObject);
            } catch (Exception $e) {
                // TODO: Handle
            }
        }
        return $data;
    }
}