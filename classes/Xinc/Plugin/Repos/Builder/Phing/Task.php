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
require_once 'Xinc/Plugin/Repos/Builder/AbstractTask.php';

class Xinc_Plugin_Repos_Builder_Phing_Task extends Xinc_Plugin_Repos_Builder_AbstractTask
{
    private $_buildFile = 'build.xml';
    private $_target = 'build';

    public function getName()
    {
        return 'phingBuilder';
    }
    public function setBuildFile($file)
    {
        $this->_buildFile = $file;
    }
    public function setTarget($target)
    {
        $this->_target = $target;
    }
    public function validateTask()
    {
        // validate if buildfile exists
        // try in working dir
        $workingdir = Xinc::getInstance()->getWorkingDir();
        $file2 = $workingdir . DIRECTORY_SEPARATOR . $this->_buildFile;
        $file = $this->_buildFile;
        
        if (!file_exists($file) && !file_exists($file2)) {
            
            Xinc_Logger::getInstance()->error('Build-File '.$file.' does not exist');
            return false;
        } else if (file_exists($file2)) {
            $this->_buildFile = $file2;
        }
        return true;
    }

    public function build(Xinc_Build_Interface &$build)
    {
        return $this->_plugin->build($build, (string)$this->_buildFile, (string)$this->_target);
    }
}