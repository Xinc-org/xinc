<?php
/**
 * Xinc - Continuous Integration.
 * Logger messages are added to the Logger queue.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Logger
 * @author    David Ellis <username@example.org>
 * @author    Gavin Foster <username@example.org>
 * @author    Arno Schneider <username@example.org>
 * @copyright 2007 David Ellis, One Degree Square
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

class Xinc_Logger_Message
{
    /**
     * @var string Priority of the message (e.g. 'info').
     */
    public $priority;
    
    /**
     * @var string Content of message.
     */
    public $message;
    
    /**
     * @var ? Timestamp of the message.
     */
    public $timestamp;
    
    /**
     * Constructor sets the priority and message content.
     *
     * @param string $strPriority Priority of the message.
     * @param ?      $timestamp   Timestamp of the message.
     * @param string $strContent  Content of the message.
     */
    public function __construct($strPriority, $timestamp, $strContent)
    {
        $this->priority = $strPriority;
        $this->timestamp = $timestamp;
        $this->message = $strContent;
    }
}