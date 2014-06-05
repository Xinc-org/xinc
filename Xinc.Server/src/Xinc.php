<?php
/**
 * Xinc - Continuous Integration.
 * The main control class.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc
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

require_once 'Xinc/Plugin/Parser.php';
require_once 'Xinc/Engine/Repository.php';
require_once 'Xinc/Build/Queue.php';
require_once 'Xinc/Build/Status/Exception/NoDirectory.php';
require_once 'Xinc/Build/Status/Exception/NonWriteable.php';

class Xinc extends \Core_Daemon
{
    const VERSION = '2.3.90';

    const DEFAULT_PROJECT_DIR = 'projects';
    const DEFAULT_STATUS_DIR = 'status';

    public $buildActive = false;

    /**
     * Registry holding all scheduled builds
     *
     * @var Xinc_Build_Queue_Interface
     */
    private static $buildQueue;

    /**
     * The actually running build
     *
     * @var Xinc_Build_Interface
     */
    private static $currentBuild;

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

    protected $loop_interval = -1;

    /**
     * @var array Holding the merged cli parameters.
     */
    private $opts = array();

    /**
     * Constructor
     */
    protected function __construct()
    {
//         self::$buildQueue = new Xinc_Build_Queue();
        parent::__construct();
    }

    /**
     * This is where you implement any once-per-execution setup code.
     * @return void
     *
     * @throws Xinc\Core\Exception\IOException if setting dirs/files failed.
     * @throws \Exception
     */
    protected function setup()
    {
        $logger = \Xinc\Core\Logger::getInstance();

        $logger->setLogLevel($this->opts['verbose']);
        $logger->setXincLogFile($this->opts['log-file']);

        $logger->info('Starting up Xinc');
        $logger->info('- Version:    ' . self::getVersion());
        $logger->info('- Workingdir: ' . $this->opts['working-dir']);
        $logger->info('- Projectdir: ' . $this->opts['project-dir']);
        $logger->info('- Statusdir:  ' . $this->opts['status-dir']);
        $logger->info('- Log Level:  ' . $this->opts['verbose']);
        $logger->info('- Daemon:     ' . ($this->is('daemonized') ? 'yes' : 'no'));

        if (isset($this->opts['working-dir'])) {
            $this->setWorkingDir($this->opts['working-dir']);
        }

        if (isset($this->opts['project-dir'])) {
            $this->setProjectDir($this->opts['project-dir']);
        }

        if (isset($this->opts['status-dir'])) {
            $this->setStatusDir($this->opts['status-dir']);
        }

        if (isset($this->opts['project-file'])) {
            $this->addProjectFiles($this->opts['project-file']);
        }

        $this->on(\Core_Daemon::ON_SHUTDOWN, array($this, 'godown'));
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
        echo "\n";
        echo 'Goodbye. Shutting down Xinc';
        echo "\n";
    }

    /**
     * @TODO send to logger
     */
    public function log($message, $label = '', $indent = 0)
    {
        if ($label !== '') {
            $message = $label . ': ' . $message;
        }
        \Xinc\Core\Logger::getInstance()->info($message);
        if ($this->is('stdout')) {
            echo $message . "\n";
        }
    }

    /**
     * Handle command line arguments.
     *
     * @return void
     */
    protected function getopt()
    {
        $workingDir = dirname($_SERVER['argv'][0]);

        $opts = getopt(
            'r:w:s:f:l:v:o',
            array(
                'project-dir:',
                'working-dir:',
                'status-dir:',
                'project-file:',
                'log-file:',
                'pid-file:', // not easy  in Core_Daemon
                'verbose:', // not easy  in Core_Daemon
                'once',  // done
                'version',  // done
                'help',  // done
                'deamon',  // not easy  in Core_Daemon
                '::',
            )
        );

        if (isset($opts['version'])) {
            $this->logVersion();
            exit();
        }

        if (isset($opts['help'])) {
            $this->show_help();
            exit();
        }

        if (isset($opts['once']) || isset($opts['o'])) {
            $this->set('daemonized', false);
        }

        $this->opts = $this->mergeOpts(
            $opts,
            array(
                'w' => 'working-dir',
                'r' => 'project-dir',
                's' => 'status-dir',
                'f' => 'project-file',
                'l' => 'log-file',
                'v' => 'verbose',
            ),
            array (
                'working-dir' => $workingDir,
                'project-dir' => $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_PROJECT_DIR . DIRECTORY_SEPARATOR,
                'status-dir'  => $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_STATUS_DIR . DIRECTORY_SEPARATOR,
                'log-file'    => $workingDir . DIRECTORY_SEPARATOR . 'xinc.log',
                'verbose'     => \Xinc\Core\Logger::DEFAULT_LOG_LEVEL,
            )
        );

        parent::getopt();
    }

    /**
     * Merges the default config and the short/long arguments given by mapping together.
     * TODO: It doesn't respect options which aren't in the mapping.
     *
     * @param array $opts The options after php getopt function call.
     * @param array $mapping Mapping from short to long argument names.
     * @param array $default The default values for some arguments.
     *
     * @return array Mapping of the long arguments to the given values.
     */
    protected function mergeOpts($opts, $mapping, $default)
    {
        $merge = $default;

        foreach ($mapping as $keyShort => $keyLong) {
            if (isset($opts[$keyShort])) {
                $merge[$keyLong] = $opts[$keyShort];
            }
            if (isset($opts[$keyLong])) {
                $merge[$keyLong] = $opts[$keyLong];
            }
        }

        return $merge;
    }

    /**
     * Sets the working directory.
     *
     * @param string $strWorkingDir The working directory.
     *
     * @throws Xinc\Core\Exception\IOException
     */
    public function setWorkingDir($strWorkingDir)
    {
        \Xinc\Core\Logger::getInstance()->verbose('Setting workingdir: ' . $strWorkingDir);
        $this->workingDir = $this->checkDirectory($strWorkingDir);
    }

    /**
     *
     * @return string
     */
    public function getProjectDir()
    {
        return $this->projectDir;
    }

    /**
     * Set the directory in which to project files lies.
     *
     * @param string $strProjectDir Directory of the project files.
     *
     * @throws Xinc\Core\Exception\IOException
     */
    public function setProjectDir($strProjectDir)
    {
        \Xinc\Core\Logger::getInstance()->verbose('Setting projectdir: ' . $strProjectDir);
        $this->projectDir = $this->checkDirectory($strProjectDir);
    }

    /**
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->workingDir;
    }

    /**
     * Set the directory in which to save project status files
     *
     * @param string $strStatusDir Directory for the status files.
     *
     * @throws Xinc\Core\Exception\IOException
     */
    public function setStatusDir($strStatusDir)
    {
        \Xinc\Core\Logger::getInstance()->verbose('Setting statusdir: ' . $strStatusDir);
        $this->statusDir = $this->checkDirectory($strStatusDir);
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
     * Checks if the directory is available otherwise tries to create it.
     * Returns the realpath of the directory afterwards.
     *
     * @param string $strDirectory Directory to check for.
     *
     * @return string The realpath of given directory.
     * @throws Xinc\Core\Exception\IOException
     */
    protected function checkDirectory($strDirectory)
    {
        if (!is_dir($strDirectory)) {
            \Xinc\Core\Logger::getInstance()->verbose(
                'Directory "' . $strDirectory . '" does not exist. Trying to create'
            );
            $bCreated = @mkdir($strDirectory, 0755, true);
            if (!$bCreated) {
                $arError = error_get_last();
                \Xinc\Core\Logger::getInstance()->verbose(
                    'Directory "' . $strDirectory . '" could not be created.'
                );
                throw new \Xinc\Core\Exception\IOException($strDirectory, null, $arError['message']);
            }
        } elseif (!is_writeable($strDirectory)) {
            \Xinc\Core\Logger::getInstance()->verbose(
                'Directory "' . $strDirectory . '" is not writeable.'
            );
            throw new \Xinc\Core\Exception\IOException(
                $strDirectory, null, null, \Xinc\Core\Exception\IOException::FAILURE_NOT_WRITEABLE
            );
        }

        return realpath($strDirectory);
    }

    public function addProjectFiles($files)
    {
        if (is_string($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            \Xinc\Core\Logger::getInstance()->info('Loading Project-File: ' . $file);
            $this->addProjectFile($file);
        }
    }





    /**
     * Processes the projects that have been configured
     * in the config-file and executes each project
     * if the scheduled time has expired
     *
     */
    public function processBuildsRunOnce()
    {
        /**
         * trigger the build queue to be populated
         */
        $nextBuildTime = Xinc::$buildQueue->getNextBuildTime();

        while (($nextBuild = Xinc::$buildQueue->getNextBuild()) !== null) {
            $nextBuild->build();
        }
    }

    /**
     * Processes the projects that have been configured
     * in the config-file and executes each project
     * if the scheduled time has expired
     *
     */
    public function processBuildsDaemon()
    {
        while (true) {
            declare(ticks = 2);
            $now = time();
            $nextBuildTime = Xinc::$buildQueue->getNextBuildTime();

            if ($nextBuildTime != null) {
                Xinc_Logger::getInstance()->info('Next buildtime: ' . date('Y-m-d H:i:s', $nextBuildTime));
                $sleep = $nextBuildTime - $now;
            } else {
                $sleep = 1;
            }
            if ($sleep > 0) {
                usleep($this->defaultSleep);
            }
            while (($nextBuild = Xinc::$buildQueue->getNextBuild()) !== null) {
                $this->buildActive = true;
                $nextBuild->build();
            }
        }
    }

    /**
     * Starts the continuous loop.
     */
    protected function start($daemon)
    {
        if ($daemon) {
            Xinc_Logger::getInstance()->info('Registering shutdown function: ' . ($res ? 'OK' : 'NOK'));
            $this->processBuildsDaemon();
        } else {
            Xinc_Logger::getInstance()->info('Run-once mode (project interval is negative)');
            //Xinc_Logger::getInstance()->flush();
            $this->processBuildsRunOnce();
        }
    }


    /**
     * Static main function called by bin script
     *
     * @param $args string argument string handled by Xinc_Config_GetOpt
     */
    public static function main($args = '')
    {
        self::$instance->start($arguments['daemon']);

        self::$instance->shutDown();
    }


    /**
     * Add a projectfile to the xinc processing
     *
     * @param string $fileName
     */
    private function addProjectFile($fileName)
    {
        try {
            $config = new \Xinc\Core\Project\Config($fileName);
            $engineName = $config->getEngineName();

            $engine = Xinc_Engine_Repository::getInstance()->getEngine($engineName);

            $builds = $engine->parseProjects($config->getProjects());

            Xinc::$buildQueue->addBuilds($builds);

        } catch (Xinc_Project_Config_Exception_FileNotFound $notFound) {
            Xinc_Logger::getInstance()->error('Project Config File ' . $fileName . ' cannot be found');
        } catch (Xinc_Project_Config_Exception_InvalidEntry $invalid) {
            Xinc_Logger::getInstance()->error('Project Config File has an invalid entry: ' . $invalid->getMessage());
        } catch (Xinc_Engine_Exception_NotFound $engineNotFound) {
            Xinc_Logger::getInstance()->error(
                'Project Config File references an unknown Engine: ' . $engineNotFound->getMessage()
            );
        }
    }

    /**
     * Returns current running build
     *
     * @return Xinc_Build_Interface
     */
    public static function getCurrentBuild()
    {
        return self::$currentBuild;
    }

    /**
     * Sets the build that is currently being processed
     *
     * @param Xinc_Build_Interface $build
     *
     * @return void
     */
    public static function setCurrentBuild(Xinc_Build_Interface $build)
    {
        self::$currentBuild = $build;
    }

    /**
     * returns the builtin properties that can be used
     * in all xinc config files
     *
     * @return array
     */
    public function getBuiltinProperties()
    {
        $properties = array();
        $properties['workingdir'] = $this->getWorkingDir();
        $properties['statusdir'] = $this->getStatusDir();
        $properties['projectdir'] = $this->getProjectDir();

        return $properties;
    }

    /**
     * prints help message, describing different parameters to run xinc
     *
     */
    protected function show_help($msg = '')
    {
        if ($msg) {
            echo 'ERROR:' . "\n" . wordwrap($msg, 72, "\n ");
        }

        echo 'Usage: xinc [switches]' . "\n\n";

        echo '  -f --project-file=<file>  The project file to use.' . "\n"
            . '  -l --log-file=<file>      The log file to use.' . "\n"
            . '  -p --pid-file=<file>      The directory to put the PID file' . "\n"
            . '  -r --project-dir=<dir>    The project directory.' . "\n"
            . '  -s --status-dir=<dir>     The status directory to use.' . "\n"
            . '  -w --working-dir=<dir>    The working directory.' . "\n"
            . '  -v --verbose=<level>      The level of information to log (default 2).' . "\n"
            . '  -o --once                 Run once and exit.' . "\n"
            . '  -d --daemon               Daemon, detach and run in the background' . "\n"
            . '  --version                 Prints the version of Xinc.' . "\n"
            . '  -h --help                 Prints this help message.' . "\n";
    }

    /**
     * Prints the version of xinc
     */
    public function logVersion()
    {
        $this->log('Xinc version ' . $this->getVersion());
    }

    /**
     * Returns the Version of Xinc
     *
     * @return string
     */
    public function getVersion()
    {
        return Xinc::VERSION;
    }
}
