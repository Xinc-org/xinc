<?php
/**
 * Xinc - Continuous Integration.
 * Iterator over an array of SimpleXMLElement objects defining Xinc Engines
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Engine
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

require_once 'Xinc/Iterator.php';
require_once 'Xinc/Project/Exception.php';


class Xinc_Engine_Iterator extends Xinc_Iterator
{
  
    /**
     * Constructor
     *
     * @param Xinc_Engine_Interface[] $array
     *
     * @throws Xinc_Engine_Exception_Invalid If array element isn't instance of
     *                                       Xinc_Engine_Interface
     */
    public function __construct(array $array)
    {
        foreach ($array as $element) {
            if (!$element instanceof Xinc_Engine_Interface ) {
                if (is_object($element)) {
                    throw new Xinc_Engine_Exception_Invalid(get_class($element));
                } else {
                    throw new Xinc_Engine_Exception_Invalid('No object');
                }
            }
            
        }
        
        parent::__construct($array);
    }
}