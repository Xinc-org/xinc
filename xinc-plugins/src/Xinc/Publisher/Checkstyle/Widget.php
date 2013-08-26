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

require_once 'Xinc/Build/Interface.php';
require_once 'Xinc/Gui/Widget/Abstract.php';
require_once 'Xinc/Gui/Widget/Repository.php';
require_once 'Xinc/Publisher/Checkstyle/Dashboard.php';
require_once 'Xinc/Publisher/Checkstyle/Statistic.php';

class Xinc_Publisher_Checkstyle_Widget extends Xinc_Gui_Widget_Abstract
{
    /**
     * Generates a short list with number of files, warnings and errors.
     *
     * @return string HTML string
     */
    public function getTestResults(Xinc_Build_Interface $build)
    {
        $numberOfFiles = $build->getStatistics()->get('checkstyle.numberOfFiles');
        if (null !== $numberOfFiles) {
            $numberOfWarnings = $build->getStatistics()->get('checkstyle.numberOfWarnings');
            $numberOfErrors = $build->getStatistics()->get('checkstyle.numberOfErrors');

            $content = $this->box('info', 'Number of files: ' . $numberOfFiles);
            if ($numberOfWarnings > 0) {
                $content .= $this->box('warning', 'Warnings: ' . $numberOfWarnings);
            }
            if ($numberOfErrors > 0) {
                $content .= $this->box('error', 'Errors: ' . $numberOfErrors);
            }

            return $content;
        } else {
            return false;
        }
    }

    private function box($type, $content)
    {
        return '<div style="height:32px;line-height:32px;margin-bottom:5px;"><div class="x-message-box-' . $type
            . '" style="width:32px;height:32px;float:left;margin-right:10px"></div>' . $content . '</div>';
    }

    /**
     * Is called after all widgets have been registered. This is the place where widgets need
     * to register the hooks for another Widget
     *
     * @return void
     */
    public function init()
    {
        $detailWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Detail');

        $extension = new Xinc_Publisher_Checkstyle_Dashboard();
        $extension->setWidget($this);

        $detailWidget->registerExtension('BUILD_DETAILS', $extension);

        $statisticWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Statistics_Widget');

        $extension = new Xinc_Publisher_Checkstyle_Statistic(
            'Checkstyle', 'line', '#f2f2f2', 'blue'
        );
        $extension->setWidget($this);

        $statisticWidget->registerExtension('STATISTIC_GRAPH', $extension);
    }
}
