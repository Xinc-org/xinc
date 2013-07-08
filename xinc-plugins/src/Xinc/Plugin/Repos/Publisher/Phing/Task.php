<?php
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

class Xinc_Plugin_Repos_Publisher_Phing_Task
    extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
    private $_buildFile = 'build.xml';
    private $_target;
    private $_workingDir = null;
    
    private $_params = null;
    
    public function getName()
    {
        return 'phingPublisher';
    }
    
    public function setBuildFile($file)
    {
        $this->_buildFile = (string) $file;
    }
    public function setTarget($target)
    {
        $this->_target = (string) $target;
    }
    
    public function setParams($params)
    {
        $this->_params = $params;
    }
    
    public function setWorkingDir($workingDir)
    {
        $this->_workingDir = $workingDir;
    }
    /**
     * Validate if all information the task needs to run
     * properly have been set
     *
     * @return boolean
     */
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

    /**
     * The parent publisher calls this method
     *
     * @param Xinc_Build_Interface $build
     *
     * @return boolean
     */
    public function publish(Xinc_Build_Interface $build)
    {
        return $this->_plugin->build($build, $this->_buildFile, $this->_target, $this->_params, $this->_workingDir);
    }
}