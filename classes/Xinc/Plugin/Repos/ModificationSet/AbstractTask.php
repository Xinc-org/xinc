<?php
/**
 * This interface represents a publishing mechanism to publish build results
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
abstract class Xinc_Plugin_Repos_ModificationSet_AbstractTask extends Xinc_Plugin_Task_Base
{
    
    public final function process(Xinc_Project &$project)
    {
        
        if ( ($status=$this->checkModified($project))===true ) {
            
            $project->setStatus(Xinc_Project_Build_Status_Interface::PASSED);
        } else if ( $status == -1 ) {
            $project->setStatus(Xinc_Project_Build_Status_Interface::STOPPED);
        } else {
            $project->setStatus(Xinc_Project_Build_Status_Interface::FAILED);
        }
        
    }
    
        /**
     * Check if this modification set has been modified
     *
     */
    public abstract function checkModified(Xinc_Project &$project);
    
    /**
     * Check necessary variables are set
     * 
     * @throws Xinc_Exception_MalformedConfig
     */
    public function validate()
    {
        try {
            return $this->validateTask();
        }
        catch(Exception $e){
            Xinc_Logger::getInstance()->error('Could not validate: '
                                             . $e->getMessage());
            return false;
        }
    }
    
    public abstract function validateTask();
    
}