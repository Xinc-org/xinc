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



class Xinc_Plugin_Repos_Gui_Statistics_Graph
{
    private $_id;
    
    private $_title;
    
    private $_type;
    
    private $_data;
    
    const TEMPLATE = 'templates/graph.html';
    
    public function __construct($title = 'Graph', $type = 'line', $data = array())
    {
        $this->_id = md5($title . $type . $data . time() . rand(0, 100000));
        $this->_title = $title;
        $this->_type = $type;
        $this->_data = $data;
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
        $data = array();
        $x = 0;
        foreach ($this->_data as $item) {
            $data[] = '{v:' . $x++ . ', label:\'' . $item['xlabel'] . '\'}' . "\n"; 
        }
        return implode(',', $data);
    }
    public function getDataSet()
    {
        $data = array();
        $x = 0;
        foreach ($this->_data as $item) {
            $data[] = '[' . $x++ . ',' . $item['y'] . ']'; 
        }
        return implode(',', $data);
    }
    
    public function generate()
    {
        $base = dirname(__FILE__);
        $filename = $base . DIRECTORY_SEPARATOR . self::TEMPLATE;
        $contents = file_get_contents($filename);
        
        $contents = str_replace(array('{ID}', '{TITLE}' , '{DATASET}' , '{TYPE}', '{XAXIS}'),
                                array($this->getId(), $this->getTitle(),
                                      $this->getDataSet(), $this->getType(),
                                      $this->getXAxis()),
                                $contents);
        return $contents;
    }
}