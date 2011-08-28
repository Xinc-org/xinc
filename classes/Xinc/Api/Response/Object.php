<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * This class holds the response of an api call
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Api
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

class Xinc_Api_Response_Object
{
    private $_response;
    
    /**
     * Set the response value for the api call
     *
     * @param string $res
     */
    public function set($res)
    {
        $this->_response = $res;
    }
    
    /**
     * Get the response value from the api call
     *
     * @return string
     */
    public function get()
    {
        return $this->_response;
    }
}