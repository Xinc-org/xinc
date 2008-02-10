<?php
/**
 * Menu Widget, displays the menu items and the current position
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';

require_once 'Xinc/Gui/Widget/Extension/Interface.php';
require_once 'Xinc/Data/Repository.php';

class Xinc_Plugin_Repos_Gui_Statistics_Graph implements Xinc_Gui_Widget_Extension_Interface
{
    private $_id;
    
    private $_title;
    
    private $_type;
    
    private $_data;
    
    private $_bgColor;
    
    private $_colorScheme;
    
    private $_labelColor;
    
    private $_customColor = array();
    
    /**
     *
     * @param string $title
     * @param string $type
     * @param string $bgColor
     * @param string $colorScheme
     * @param string $labelColor
     */
    public function __construct($title = 'Graph', $type = 'line',
                                $bgColor = '#f2f2f2', $colorScheme = 'blue',
                                $labelColor = '#000000')
    {
        $this->_id = md5($title . $type . $bgColor . $colorScheme . $labelColor . time() . rand(0, 100000));
        $this->_title = $title;
        $this->_type = $type;
        $this->_bgColor = $bgColor;
        $this->_colorScheme = $colorScheme;
        $this->_labelColor = $labelColor;
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
    public function generate($data = array(), $colorScheme = array())
    {
        $this->_data = $data;
        $this->_customColors = $colorScheme;
        $base = dirname(__FILE__);
        $filename = $this->_getTemplateFileName($this->_type);
        
        ob_start();
        include_once($filename);
        
       $contents = ob_get_clean();
       return $contents;
    }
    
    public function getBgColor()
    {
        return $this->_bgColor;
    }
    
    public function getColorScheme()
    {
        return $this->_colorScheme;
    }
    
    public function getLabelColor()
    {
        return $this->_labelColor;
    }
    
    public function getExtensionPoint()
    {
        return 'STATISTIC_GRAPH';
    }
}