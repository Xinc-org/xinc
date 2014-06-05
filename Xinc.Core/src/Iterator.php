<?php
/**
 * Xinc - Continuous Integration.
 * Iterator over an array of elements
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Iterator
 * @author    Arno Schneider <username@example.com>
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
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Core;

class Iterator
{
    /**
     * Internal array
     *
     * @var array
     */
    protected $array;
    
    /**
     * Pointer for the current index
     *
     * @var integer
     */
    private $pointer = 0;
    
    /**
     * size of the array
     *
     * @var integer
     */
    protected $size = 0;
    
    public function __construct($array = array())
    {
        $this->array = $array;
        $this->size = count($this->array);
    }
    
    public function add($item)
    {
        $this->array[] = $item;
        $this->size++;
    }
    
    public function hasNext()
    {
        return $this->pointer < $this->size;
    }
    
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     * @return Xinc_Build_Interface
     */
    public function &next()
    {
        return $this->array[$this->pointer++];
    }
    
    public function count()
    {
        return $this->size;
    }
}