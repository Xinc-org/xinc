<?php
/**
 * This interface represents a publishing mechanism to publish build results
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
require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Plugin_Repos_Publisher_Deliverable_Task extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
    protected $_file;
    
    protected $_alias;
    
    
    public function getName()
    {
        return 'deliverable';
    }
    public function setFile($file)
    {
        $this->_file = $file;
    }
    
    public function setAlias($alias)
    {
        $this->_alias = $alias;
    }
    
    public function validateTask()
    {
        
        if (!isset($this->_file)) {
            
            Xinc_Logger::getInstance()->error('File must be specified for deliverable publisher.');
            return false;
        }
        return true;
    }

    public function publish(Xinc_Build_Interface &$build)
    {
        return $this->_plugin->registerDeliverable($build, $this->_file, $this->_alias);
    }
}