<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Statistics
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

require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';

require_once 'Xinc/Gui/Widget/Extension/Interface.php';
require_once 'Xinc/Data/Repository.php';

abstract class Xinc_Plugin_Repos_Gui_Statistics_Graph
    implements Xinc_Gui_Widget_Extension_Interface
{
    private $_id;

    private $_title;

    private $_type;

    private $_data;

    private $_bgColor;

    private $_colorScheme;

    private $_labelColor;

    private $_customColor = array();

    protected $_widget;

    /**
     *
     * @param string $title
     * @param string $type
     * @param string $bgColor
     * @param string $colorScheme
     * @param string $labelColor
     */
    public function __construct(
        $title = 'Graph', $type = 'line', $bgColor = '#f2f2f2',
        $colorScheme = 'blue', $labelColor = '#000000'
    ) {
        $this->_id = md5($title . $type . $bgColor . $colorScheme . $labelColor);
        $this->_title = $title;
        $this->_type = $type;
        $this->_bgColor = $bgColor;
        $this->_colorScheme = $colorScheme;
        $this->_labelColor = $labelColor;
    }

    private function _initEzcGraph()
    {
        require_once 'ezc/Graph/graph.php';
        require_once 'ezc/Base/exceptions/exception.php';
        require_once 'ezc/Base/exceptions/property_not_found.php';

        require_once 'ezc/Base/exceptions/value.php';
        require_once 'ezc/Graph/interfaces/palette.php';

        require_once 'Xinc/Plugin/Repos/Gui/Statistics/Graph/Palette.php';

        require_once 'ezc/Graph/interfaces/chart.php';
        require_once 'ezc/Base/base.php';
        require_once 'ezc/Base/options.php';
        require_once 'ezc/Graph/options/driver.php';
        require_once 'ezc/Graph/options/svg_driver.php';
        require_once 'ezc/Graph/colors/color.php';
        require_once 'ezc/Graph/colors/linear_gradient.php';
        require_once 'ezc/Graph/data_container/base.php';
        require_once 'ezc/Graph/data_container/single.php';
        require_once 'ezc/Graph/math/boundings.php';
        require_once 'ezc/Graph/interfaces/element.php';
        require_once 'ezc/Graph/element/background.php';
        require_once 'ezc/Graph/element/text.php';
        require_once 'ezc/Graph/element/legend.php';
        require_once 'ezc/Graph/interfaces/driver.php';
        require_once 'ezc/Graph/driver/svg_font.php';
        require_once 'ezc/Graph/driver/svg.php';
        require_once 'ezc/Graph/interfaces/palette.php';
        require_once 'ezc/Graph/palette/tango.php';
        require_once 'ezc/Graph/axis/container.php';
        require_once 'ezc/Graph/options/font.php';
        require_once 'ezc/Graph/options/chart.php';
        require_once 'ezc/Graph/options/line_chart.php';
        require_once 'ezc/Graph/charts/line.php';
        require_once 'ezc/Graph/options/pie_chart.php';
        require_once 'ezc/Graph/charts/pie.php';
        require_once 'ezc/Base/struct.php';
        require_once 'ezc/Graph/structs/coordinate.php';
        require_once 'ezc/Graph/options/renderer.php';
        require_once 'ezc/Graph/options/renderer_2d.php';
        require_once 'ezc/Graph/options/renderer_3d.php';

        require_once 'ezc/Graph/interfaces/renderer.php';
        require_once 'ezc/Graph/interfaces/radar_renderer.php';
        require_once 'ezc/Graph/interfaces/stacked_bar_renderer.php';
        require_once 'ezc/Graph/interfaces/odometer_renderer.php';
        require_once 'ezc/Graph/renderer/2d.php';
        require_once 'ezc/Graph/renderer/3d.php';
        require_once 'ezc/Graph/element/axis.php';
        require_once 'ezc/Graph/axis/labeled.php';
        require_once 'ezc/Graph/interfaces/axis_label_renderer.php';
        require_once 'ezc/Graph/renderer/axis_label_centered.php';
        require_once 'ezc/Graph/renderer/axis_label_exact.php';
        require_once 'ezc/Graph/axis/numeric.php';
        require_once 'ezc/Graph/datasets/base.php';
        require_once 'ezc/Graph/datasets/array.php';
        require_once 'ezc/Graph/interfaces/dataset_property.php';
        require_once 'ezc/Graph/datasets/property/string.php';
        require_once 'ezc/Graph/datasets/property/integer.php';
        require_once 'ezc/Graph/datasets/property/boolean.php';
        require_once 'ezc/Graph/datasets/property/color.php';
        require_once 'ezc/Graph/datasets/property/axis.php';
        require_once 'ezc/Graph/structs/step.php';
        require_once 'ezc/Graph/math/vector.php';
        require_once 'ezc/Graph/structs/context.php';

        require_once 'ezc/Graph/exceptions/exception.php';
        require_once 'ezc/Graph/exceptions/no_such_data.php';
        require_once 'ezc/Graph/exceptions/reducement_failed.php';
    }

    public function setWidget(Xinc_Gui_Widget_Interface &$widget)
    {
        $this->_widget = $widget;
    }

    private function _getTemplateFileName($type = 'line')
    {
        $base = Xinc_Data_Repository::getInstance()->get('templates' . DIRECTORY_SEPARATOR . 'statistics');
        switch ($type) {
            case 'line':
            default:
                return $base . DIRECTORY_SEPARATOR . 'linegraph.phtml';
                break;
        }
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getXAxis()
    {
        $dataSet = array();
        $x = 0;
        foreach ($this->_data as $sitem) {
            foreach ($sitem as $item) {
                $dataSet[] = '{v:' . $x++ . ', label:\'' . $item['xlabel'] . '\'}' . "\n";
            } 
            break;
        }
        return implode(',', $dataSet);
    }

    public function getDataSet(array $data)
    {
        $dataSet = array();
        $x = 0;
        foreach ($data as $item) {
            $dataSet[] = '[' . $x++ . ',' . $item['y'] . ']'; 
        }
        return implode(',', $dataSet);
    }

    public function getDataSets()
    {
        return $this->_data;
    }

    public abstract function buildDataSet(
        Xinc_Project &$project, array $buildHistory = array(), $previousData = array()
    );

    public function generate($data = array(), $colorScheme = array())
    {
        $this->_data = $data;
        $this->_customColors = $colorScheme;

        $this->_colorScheme = $this->getColorScheme();
        $this->_initEzcGraph();
        try {
            switch ($this->_type) {
                case 'pie':
                    $graph = new ezcGraphPieChart();
                    $graph->renderer = new ezcGraphRenderer3d(); 
                    break;
                case 'line':
                default:
                    $graph = new ezcGraphLineChart();
            }

            $graph->title = "";//$this->getTitle(); 
            //$graph->background->color = $this->getBgColor();
            //$keyNo = 0;
            if (count($this->_colorScheme)>0) {
                $graph->palette = new Xinc_Plugin_Repos_Gui_Statistics_Graph_Palette($this->_colorScheme);
            }
            foreach ( $data as $key => $value )
            {
                 $graph->data[$key] = new ezcGraphArrayDataSet( $value );
                 /**if (isset($this->_colorScheme[$key])) {
                     $graph->data[$key]->color[0] = $this->_colorScheme[$key]; 
                 } else if (isset($this->_colorScheme[$keyNo])) {
                     $graph->data[$key]->color[0] = $this->_colorScheme[$keyNo]; 
                 }
                 $keyNo++;*/
             } 
             //echo "Writing id: " . $this->getId();
             $fileName = $this->_widget->getGraphFileName($this->getId());
             //echo " filename: $fileName";
             $width = 375;
             $height = 180;
             $graph->render( $width, $height, $fileName); 
             $includeString = '<div style="position:relative;padding-left:10px;float:left" class="none"><h3>'.$this->getTitle().'</h3><iframe src="/statistics/graph/?project=' . $this->_widget->getProjectName() . '&name=' . $this->getId() . '" width="'.$width.'" height="'.$height.'" border="0" frameborder="0"></iframe></div>';
             return $includeString;
        } catch (Exception $e) {
            //var_dump($e);
        }
    }

    public function getBgColor()
    {
        return $this->_bgColor;
    }

    public abstract function getColorScheme();

    public function getLabelColor()
    {
        return $this->_labelColor;
    }

    public function getExtensionPoint()
    {
        return 'STATISTIC_GRAPH';
    }
}