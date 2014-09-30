<?php
/**
 * Xinc - Continuous Integration.
 * The main control class.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Server
 * @author    David Ellis  <username@example.org>
 * @author    Gavin Foster <username@example.org>
 * @author    Jamie Talbot <username@example.org>
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
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
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Server;

class Daemon extends \Core_Daemon
{
    /**
     * Current working directory
     * containing the default xinc projects
     *
     * @var string
     */
    private $workingDir;

    /**
     * Directory holding the projects
     *
     * @var string
     */
    private $projectDir;

    /**
     * The directory to drop xml status files
     *
     * @var string
     */
    private $statusDir;

    protected $loop_interval = 5;


    /**
     * Implement this method to define workers
     *
     * @return void
     */
    protected function setup_workers()
    {
        $engines = Engine\Repository::getInstance()->getEngines();
        foreach ($engines as $name => $engine) {
            if ($name !== $engine->getName()) {
                continue;
            }
            $this->worker('Sunrise', $engine);
            $this->$name->workers(1);
            $this->$name->timeout(0);
            // $this->$name->setup();
        }
    }

    /**
     * This is where you implement any once-per-execution setup code.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function setup()
    {
        $this->on(\Core_Daemon::ON_SHUTDOWN, array($this, 'godown'));

        $engines = Engine\Repository::getInstance()->getEngines();
        foreach ($engines as $name => $engine) {
            if ($name !== $engine->getName()) {
                continue;
            }
            $this->$name->doWork();
        }
    }

    /**
     * This is where you implement the tasks you want your daemon to perform.
     * This method is called at the frequency defined by loop_interval.
     *
     * @return void
     */
    protected function execute()
    {
    }

    /**
     * Dynamically build the file name for the log file. This simple algorithm
     * will rotate the logs once per day and try to keep them in a central /var/log location.
     *
     * @return string
     */
    protected function log_file()
    {
    }

    /**
     * Shutdown the xinc instance.
     *
     * @param boolean $exit
     */
    protected function godown()
    {
        if ($this->is('parent')) {
            $this->log('Goodbye. Shutting down Xinc');
        }
    }

    /**
     * Writes all log messages to the Xinc logger as info. And writes it to console if available.
     * TODO: No difference between error, warning, info yet ... PSR3 logger needed.
     */
    public function log($message, $label = '', $indent = 0)
    {
        if ($label !== '') {
            $message = $label . ': ' . $message;
        }

        \Xinc\Core\Logger::getInstance()->info($message);
    }

    public function setRunOnce()
    {
        $this->set('daemonized', false);
    }

    /**
     * Sets the working directory.
     *
     * @param string $workingDir The working directory.
     *
     * @return void
     */
    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;
    }

    /**
     * Gets the working directory.
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->workingDir;
    }

    /**
     * Set the directory in which to project files lies.
     *
     * @param string $strProjectDir Directory of the project files.
     *
     * @return void
     */
    public function setProjectDir($projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Returns the directory in which to project files lies.
     *
     * @return string
     */
    public function getProjectDir()
    {
        return $this->projectDir;
    }

    /**
     * Set the directory in which to save project status files
     *
     * @param string $statusDir Directory for the status files.
     *
     * @return void
     */
    public function setStatusDir($statusDir)
    {
        $this->statusDir = $statusDir;
    }

    /**
     * Returns the  Directory for the status files.
     *
     * @return string Directory for the status files.
     */
    public function getStatusDir()
    {
        return $this->statusDir;
    }

    /**
     * Adds all given project files to the daemon.
     *
     * TODO: This seams not the work of this class.
     *
     * @param array|string $files
     *
     * @return void
     */
    public function addProjectFiles($files)
    {
        if (is_string($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $this->addProjectFile($file);
        }
    }

    /**
     * Add a projectfile to the xinc processing
     *
     * @param string $fileName
     *
     * @return void
     */
    private function addProjectFile($fileName)
    {
        \Xinc\Core\Logger::getInstance()->info('Loading Project-File: ' . $fileName);

        try {
            $config = new \Xinc\Core\Project\Config($fileName);
            $group = $config->getProjectGroup();

            foreach ($group->getProjects() as $project) {
                $engine = Engine\Repository::getInstance()->getEngine($project->getEngineName());
                $engine->addProject($project);
            }
        } catch (\Xinc\Core\Project\Config\Exception\FileNotFoundException $notFound) {
            \Xinc\Core\Logger::getInstance()->error('Project Config File ' . $fileName . ' cannot be found');
        } catch (Xinc_Engine_Exception_NotFound $engineNotFound) {
            \Xinc\Core\Logger::getInstance()->error(
                'Project Config File references an unknown Engine: ' . $engineNotFound->getMessage()
            );
        }
    }
}
