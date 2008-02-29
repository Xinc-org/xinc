<?php
/**
 * PUT DESCRIPTION HERE
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
require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Publisher/Email/Task.php';

class Xinc_Plugin_Repos_Publisher_Email  extends Xinc_Plugin_Base
{
    
    private function _sendPearMail($from, $to, $subject, $message)
    {
        require_once 'Xinc/Ini.php';
        $smtpSettings = Xinc_Ini::getInstance()->get('email_smtp');
        if ($smtpSettings != null) {
            $mailer = Mail::factory('smtp', $smtpSettings);
        } else {
            $mailer = Mail::factory('mail');
        }
        $recipients = split(',', $to);
        $headers = array();
        $headers['From'] = $from;
        $headers['Subject'] = $subject;
        $res = $mailer->send($recipients, $headers, $message);
        if ($res === true) {
            return $res;
        } else {
            return false;
        }
    }
    
    public function validate()
    {
        
        return true;
    }
    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Publisher_Email_Task($this));
    }
    public function email(Xinc_Project &$project,$to,$subject,$message, $from = 'Xinc')
    {
        $project->info('Executing email publisher with content ' 
                      ."\nTo: " . $to
                      ."\nSubject: " . $subject
                      ."\nMessage: " . $message
                      ."\nFrom: " . $from);

        /** send the email */
        
        @include_once 'Mail.php';
        
        if (class_exists('Mail')) {
            return $this->_sendPearMail($from, $to, $subject, $message);
        } else {
            
            $res = mail($to, $subject, $message, "From: $from\r\n");
            if ($res) {
                $project->info('Email sent successfully');
                return true;
            } else {
                $project->error('Email could not be sent');
                return false;
                //$project->setStatus(Xinc_Build_Interface::FAILED);
            }
        }
    }
}