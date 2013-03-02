<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Exception, logging file is not writeable
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Logger.Exception
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

class Xinc_Logger_Exception_NonWriteable extends Exception
{
    /**
     * Constructor, generates an Exception Message
     *
     * @param string $strFileName Name of file which isn't writeable.
     */
    public function __construct($strFileName)
    {
        parent::__construct('Log File ' . $strFileName . ' is not writeable');
    }
}