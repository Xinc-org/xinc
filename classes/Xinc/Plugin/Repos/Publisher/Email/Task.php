<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * This interface represents a publishing mechanism to publish build results
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Publisher
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
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Plugin_Repos_Publisher_Email_Task
    extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
   
    private $_to;
    private $_from;
    private $_subject;
    private $_message;
    public function getName()
    {
        return 'email';
    }
    
    /**
     * Set the email address to send to
     *
     * @param string $subject
     */
    public function setTo($to)
    {
        $this->_to = (string)$to;
    }
    /**
     * Set the email address to send to
     *
     * @param string $subject
     */
    public function setFrom($from)
    {
        $this->_from = (string)$from;
    }
    /**
     * Set the subject of the email
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->_subject = (string)$subject;
    }
    
    /**
     * Set the message of the email
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->_message = (string)$message;
    }
    
    public function validateTask()
    {
        
        if (!isset($this->_to)) {
              throw new Xinc_Exception_MalformedConfig('Element publisher/email - required attribute '
                                                      .'\'to\' is not set');
        }
        if (!isset($this->_subject)) {
            throw new Xinc_Exception_MalformedConfig('Element publisher/email - required attribute '
                                                    .'\'subject\' is not set');
        }
        if (!isset($this->_message)) {
            throw new Xinc_Exception_MalformedConfig('Element publisher/email - required attribute '
                                                    .'\'message\' is not set');
        }
        return true;
    }
    
    public function publish(Xinc_Build_Interface $build)
    {
        $statusBefore = $build->getStatus();
        $res = $this->_plugin->email($build->getProject(), $this->_to, $this->_subject, $this->_message, $this->_from);
    }
}