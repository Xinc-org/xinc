<?php
/**
 * Xinc - Continuous Integration.
 * Iterator over an array of Xinc_Plugin_Task_Interface objects
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build.Task
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
require_once 'Xinc/Build/Exception/Invalid.php';

class Xinc_Build_Tasks_Iterator extends Xinc_Iterator
{
    /**
     * Adds a task to the iterator.
     *
     * @param object $item
     * @throws Xinc_Build_Exception_Invalid
     */
    public function add($item)
    {
        if (!$item instanceof Xinc_Plugin_Task_Interface) {
            throw new Xinc_Build_Exception_Invalid();
        }
        parent::add($item);
    }
}