<?php
/**
 * Iterator over an array of elements
 * 
 * @package Xinc
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
class Xinc_Iterator
{
    /**
     * Internal array
     *
     * @var array
     */
    protected $_array;
    
    /**
     * Pointer for the current index
     *
     * @var integer
     */
    private $_pointer = 0;
    
    /**
     * size of the array
     *
     * @var integer
     */
    protected $_size = 0;
    
    public function __construct($array = array())
    {
        $this->_array = $array;
        $this->_size = count($this->_array);
    }
    
    public function add($item)
    {
        $this->_array[] = $item;
        $this->_size++;
    }
    
    public function hasNext()
    {
        return $this->_pointer < $this->_size;
    }
    
    public function rewind()
    {
        $this->_pointer = 0;
    }
    /**
     *
     * @return Xinc_Build_Interface
     */
    public function &next()
    {
        return $this->_array[$this->_pointer++];
    }
    
    public function count()
    {
        return $this->_size;
    }
}