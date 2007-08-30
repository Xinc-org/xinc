<?php
/**
 * This class represents a publisher that emails the project status.
 * 
 * @package Xinc.Publishers
 * @author David Ellis
 * @author Gavin Foster
 * @version 1.0
 * @copyright 2007 David Ellis, One Degree Square
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *	This file is part of Xinc.
 *	Xinc is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU Lesser General Public License as published by
 *	the Free Software Foundation; either version 2.1 of the License, or
 *	(at your option) any later version.
 *
 *	Xinc is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Lesser General Public License for more details.
 *
 *	You should have received a copy of the GNU Lesser General Public License
 *	along with Xinc, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Publisher/Interface.php';
require_once 'Xinc/Logger.php';

class Xinc_Publisher_Email implements Xinc_Publisher_Interface
{
    /**
	 * Email address to send to. 
	 *
	 * @var string
	 */
    private $to;
    
    /**
	 * Subject of email. 
	 *
	 * @var string
	 */
    private $subject;

    /**
     * Message to send
     *
     * @var string
     */
    private $message;

    /**
  	 * Status on which to execute this publisher
  	 *
  	 * @var boolean
  	 */
	private $publishOnSuccess = false;
	private $publishOnFailure = false;

    /**
	 * Set the email address to send to
	 *
	 * @param string $subject
	 */
    public function setTo($to)
    {
        $this->to = $to;
    }
    
    /**
	 * Set the subject of the email
	 *
	 * @param string $subject
	 */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    /**
	 * Set the message of the email
	 *
	 * @param string $message
	 */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
  	 * Set whether to publish on a successful build
  	 *
  	 * @param boolean $publishOnSuccess
  	 */
  	public function setPublishOnSuccess($publishOnSuccess)
  	{
  		$this->publishOnSuccess = $publishOnSuccess;
  	}
  	
  	/**
  	 * Set whether to publish on a unsuccessful build
  	 *
  	 * @param boolean $publishOnFailure
  	 */
  	public function setPublishOnFailure($publishOnFailure)
  	{
  		$this->publishOnFailure = $publishOnFailure;
  	}

    /**
  	 * Given the status of the last build (true/false) this method will return
  	 * a boolean describing whether its publish method should be executed or not.
  	 *
  	 * @param boolean $buildStatus
  	 * @return boolean
  	 */
    public function publishOn($buildStatus)
    {
        if ($buildStatus && $this->publishOnSuccess) {
  	        return true;
  	    } elseif (!$buildStatus && $this->publishOnFailure) {
            return true;
  	    } else {
  	        return false;
  	    }
    }

    /**
  	 * Publish the build.  (This one uses Phing, but Email and file copies are alternative options).
  	 *
  	 */
    public function publish()
    {
        Xinc_Logger::getInstance()->info('Executing email publisher with content ' . 
                                         "\nTo: " . $this->to . 
                                         "\nSubject: " . $this->subject . 
                                         "\nMessage: " . $this->message);

        /** send the email */
        mail($this->to, $this->subject, $this->message);
    }
    
    
	/**
	 * Check necessary variables are set
	 *
	 * @throws Xinc_Exception_MalformedConfig
	 */
	public function validate()
	{
	    if (!isset($this->to)) {
  	        throw new Xinc_Exception_MalformedConfig('Element publisher/email - required attribute \'to\' is not set');
  	    }
  	    if (!isset($this->subject)) {
  	        throw new Xinc_Exception_MalformedConfig('Element publisher/email - required attribute \'subject\' is not set');
  	    }
  	    if (!isset($this->message)) {
  	        throw new Xinc_Exception_MalformedConfig('Element publisher/email - required attribute \'message\' is not set');
  	    }
	}
}
