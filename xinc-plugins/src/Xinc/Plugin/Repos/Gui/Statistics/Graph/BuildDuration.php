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

class Xinc_Plugin_Repos_Gui_Statistics_Graph_BuildDuration extends Xinc_Plugin_Repos_Gui_Statistics_Graph
{
    public function getBgColor()
    {
        return '#000000';
    }

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
            $data = array('Successful Builds'=>array(),'Failed Builds'=>array());
        }
        $builds = array();
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize(
                    $project,
                    $buildTimestamp,
                    Xinc_Gui_Handler::getInstance()->getStatusDir()
                );
                $duration = $buildObject->getStatistics()->get('build.duration');
                $buildNo = $buildObject->getNumber();
                if (!is_numeric($duration)) {
                    $duration = 0;
                }
                if (isset($builds[$buildNo])) {
                    $builds[$buildNo]++;
                    $buildNo .= '.f' . $builds[$buildNo];
                } else {
                    $builds[$buildNo] = 0;
                }
                if ($buildObject->getStatus() == Xinc_Build_Interface::PASSED) {
                    $data['Successful Builds'][$buildNo] = $duration;
                    $data['Failed Builds'][$buildNo] = 0;
                } else {
                    $data['Failed Builds'][$buildNo] = $duration;
                    $data['Successful Builds'][$buildNo] = 0;
                }
                $prevBuildNo = $buildNo;
                unset($buildObject);
            } catch (Exception $e) {
                // TODO: Handle
            }
        }
        return $data;
    }
}