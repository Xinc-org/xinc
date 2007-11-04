<?php
/**
 * PUT DESCRIPTION HERE
 * 
 * @package Xinc
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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
    
   
    public function validate()
    {
       
        return true;
    }
    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Publisher_Email_Task($this));
    }
    public function email(Xinc_Project &$project,$to,$subject,$message)
    {
        $project->info('Executing email publisher with content ' 
                      ."\nTo: " . $to
                      ."\nSubject: " . $subject
                      ."\nMessage: " . $message);

        /** send the email */
        $res=mail($to, $subject, $message);
        if ($res) {
            $project->info('Email sent successfully');
            return true;
        } else {
            $project->error('Email could not be sent');
            return false;
            //$project->setStatus(Xinc_Project_Build_Status_Interface::FAILED);
        }
    }
}