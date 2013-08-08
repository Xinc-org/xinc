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

class Xinc_Plugin_Repos_Publisher_Phing_Task extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
    private $buildFile = 'build.xml';
    private $target;
    private $workingDir = null;

    private $params = null;

    public function getName()
    {
        return 'phingPublisher';
    }

    public function setBuildFile($file)
    {
        $this->buildFile = (string) $file;
    }

    public function setTarget($target)
    {
        $this->target = (string) $target;
    }

    public function setParams($params)
    {
        $this->params = (string) $params;
    }

    public function setWorkingDir($workingDir)
    {
        $this->workingDir = (string) $workingDir;
    }

    /**
     * Validate if all information the task needs to run properly have been set
     *
     * @return boolean True if task can be started otherwise false.
     */
    public function validateTask()
    {
        // validate if buildfile exists
        // try in working dir
        $buildFileWorking = $this->workingDir . DIRECTORY_SEPARATOR . $this->buildFile;

        if (!file_exists($this->buildFile) && !file_exists($buildFileWorking)) {
            Xinc_Logger::getInstance()->error('Build-File ' . $this->buildFile . ' does not exist');
            return false;
        } elseif (file_exists($buildFileWorking)) {
            $this->buildFile = $buildFileWorking;
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
        return $this->plugin->build($build, $this->buildFile, $this->target, $this->params, $this->workingDir);
    }
}
