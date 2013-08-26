<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Publisher
 * @subpackage Checkstyle
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2013 Alexander Opitz, Leipzig
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *             This file is part of Xinc.
 *             Xinc is free software; you can redistribute it and/or modify
 *             it under the terms of the GNU Lesser General Public License as
 *             published by the Free Software Foundation; either version 2.1 of
 *             the License, or (at your option) any later version.
 *
 *             Xinc is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Lesser General Public License for more details.
 *
 *             You should have received a copy of the GNU Lesser General Public
 *             License along with Xinc, write to the Free Software Foundation,
 *             Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link       http://code.google.com/p/xinc/
 */

require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph.php';

class Xinc_Publisher_Checkstyle_Statistic extends Xinc_Plugin_Repos_Gui_Statistics_Graph
{
    public function getBgColor()
    {
        return '#000000';
    }

    public function getColorScheme()
    {
        return array('#0000dd','#ffff00', '#ff0000');
    }

    public function buildDataSet(
        Xinc_Project $project, array $buildHistoryArr = array(), $previousData = array()
    ) {
        if (count($previousData)>0) {
            $data = $previousData;
        } else {
            $data = array(
                'Files'=>array(),
                'Warnings'=>array(),
                'Errors'=>array(),
            );
        }
        $builds = array();
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize(
                    $project,
                    $buildTimestamp,
                    Xinc_Gui_Handler::getInstance()->getStatusDir()
                );
                $numberOfFiles = $buildObject->getStatistics()->get('checkstyle.numberOfFiles');
                if (null === $numberOfFiles) {
                    continue;
                }
                $buildNo = $buildObject->getNumber();
                if (isset($builds[$buildNo])) {
                    $builds[$buildNo]++;
                    $buildNo .= '.f' . $builds[$buildNo];
                } else {
                    $builds[$buildNo] = 0;
                }
                $data['Files'][$buildNo] = $numberOfFiles;
                $data['Warnings'][$buildNo] = $buildObject->getStatistics()->get('checkstyle.numberOfWarnings');
                $data['Errors'][$buildNo] = $buildObject->getStatistics()->get('checkstyle.numberOfErrors');

                $prevBuildNo = $buildNo;
                unset($buildObject);
            } catch (Exception $e) {
                // TODO: Handle
            }
        }

        return $data;
    }
}
