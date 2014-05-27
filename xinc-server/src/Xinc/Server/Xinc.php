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

require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/IO.php';
require_once 'Xinc/Exception/MalformedConfig.php';
require_once 'Xinc/Plugin/Parser.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/Config.php';
require_once 'Xinc/Project/Config.php';
require_once 'Xinc/Engine/Repository.php';
require_once 'Xinc/Build/Queue.php';
require_once 'Xinc/Build/Status/Exception/NoDirectory.php';
require_once 'Xinc/Build/Status/Exception/NonWriteable.php';
require_once 'Xinc/Timezone.php';

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

    /**
     * The file with the pid marker
     *
     * @var string
     */
    private $pidFile;

    /**
     * Instance of this object.
     *
     * @var Xinc
     */
    private static $instance;

    /**
     * Timezone of system
     *
     * @var Xinc_Timezone
     */
    private static $systemTimezone;

    /**
     * Holding the config settings
     *
     * @var array
     */
    private $config = array();

    protected  $loop_interval = -1;

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
     * @throws \Exception
     */
    protected function setup()
    {
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
    public function log($message, $label = '', $indent = 0) {
        echo 'Message: ' . $message . "\n";
    }

    /**
     * Handle command line arguments.
     *
     * @return void
     */
    protected function getopt()
    {
        $opts = getopt(
            'p:w:s:f:l:v:o',
            array(
                'project-dir:',
                'working-dir:',
                'status-dir:',
                'config-file:',
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

var_dump($opts);

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

        parent::getopt();
    }

    public function getSystemTimezone()
    {
        return self::$systemTimezone;
    }

    /**
     * Specify a config file to be parsed for project definitions.
     *
     * @param string $fileName
     * @throws Xinc_Exception_MalformedConfig
     */
    private function setSystemConfigFile($fileName)
    {
        $realFileName = realpath($fileName);
        if (false === $realFileName) {
            throw new Xinc_Exception_MalformedConfig('System config file: ' . $fileName . ' not found.');
        }
        $configFile = Xinc_Config_File::load($realFileName);

        $configParser = new Xinc_Config_Parser($configFile);

        $plugins = $configParser->getPlugins();
        $engines = $configParser->getEngines();
        $configSettings = $configParser->getConfigSettings();

        $pluginParser = new Xinc_Plugin_Parser();
        $pluginParser->parse($plugins);

        $this->engineParser = new Xinc_Engine_Parser();
        $this->engineParser->parse($engines);

        while ($configSettings->hasNext()) {
            $setting = $configSettings->next();
            $attributes = $setting->attributes();
            $name = (string) $attributes->name;
            $value = (string) $attributes->value;
            if ($name == 'loglevel' && Xinc_Logger::getInstance()->logLevelSet()) {
                $value = Xinc_Logger::getInstance()->getLogLevel();
            }
            self::getInstance()->setConfigDirective($name, $value);
        }
    }


    private function setConfigDirective($name, $value)
    {
        $this->config[$name] = $value;
        switch ($name) {
            case 'loglevel':
                Xinc_Logger::getInstance()->setLogLevel($value);
                break;
            case 'timezone':
                Xinc_Timezone::set($value);
                break;
            default:
        }
    }

    public function restoreConfigDirectives()
    {
        foreach ($this->config as $name => $value) {
            $this->setConfigDirective($name, $value);
        }
        /**
         * restore timezone, if system.xml does not configure one
         */
        if ($this->getConfigDirective('timezone') === null) {
            Xinc_Timezone::reset();
        }
    }

    public function getConfigDirective($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    public function setPidFile($pidFile)
    {
        $this->pidFile = $pidFile;
    }

    /**
     * Set the directory in which to save project status files
     *
     * @param string $strStatusDir Directory for the status files.
     *
     * @throws Xinc_Exception_IO
     */
    public function setStatusDir($strStatusDir)
    {
        Xinc_Logger::getInstance()->verbose('Setting statusdir: ' . $strStatusDir);
        $this->statusDir = $this->checkDirectory($strStatusDir);
    }


    /**
     * Checks if the directory is available otherwise tries to create it.
     * Returns the realpath of the directory afterwards.
     *
     * @param string $strDirectory Directory to check for.
     *
     * @return string The realpath of given directory.
     * @throws Xinc_Exception_IO
     */
    protected function checkDirectory($strDirectory)
    {
        if (!is_dir($strDirectory)) {
            Xinc_Logger::getInstance()->verbose(
                'Directory "' . $strDirectory . '" does not exist. Trying to create'
            );
            $bCreated = @mkdir($strDirectory, 0755, true);
            if (!$bCreated) {
                $arError = error_get_last();
                Xinc_Logger::getInstance()->verbose(
                    'Directory "' . $strDirectory . '" could not be created.'
                );
                throw new Xinc_Exception_IO($strDirectory, null, $arError['message']);
            }
        } elseif (!is_writeable($strDirectory)) {
            Xinc_Logger::getInstance()->verbose(
                'Directory "' . $strDirectory . '" is not writeable.'
            );
            throw new Xinc_Exception_IO($strDirectory, null, null, Xinc_Exception_IO::FAILURE_NOT_WRITEABLE);
        }

        return realpath($strDirectory);
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

    private function isProcessRunning($pid)
    {
        if (isset($_SERVER['SystemRoot']) && DIRECTORY_SEPARATOR != '/') {
            /**
             * winserv is handling that
             */
            return false;
        } else {
            exec('ps --no-heading -p ' . $pid, $out, $res);
            if ($res != 0) {
                return false;
            } else {
                if (count($out) > 0) {
                    return true;
                } else {
                    return false;
                }
            }
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
        /**
         * write pid file
         */
        if (file_exists($this->pidFile)) {
            $oldPid = file_get_contents($this->pidFile);
            if ($this->isProcessRunning($oldPid)) {
                Xinc_Logger::getInstance()->error(
                    'Xinc Instance with PID ' . $pid . ' still running. Check pidfile '
                    . $this->pidFile . '. Shutting down.'
                );
                exit(-1);
            } else {
                Xinc_Logger::getInstance()->error('Cleaning up old pidFile.');
            }
        }
        file_put_contents($this->pidFile, getmypid());
        while (true) {
            declare(ticks = 2);
            $now = time();
            $nextBuildTime = Xinc::$buildQueue->getNextBuildTime();
            Xinc_Timezone::reset();

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
     * Sets the working directory.
     *
     * @param string $strWorkingDir The working directory.
     *
     * @throws Xinc_Exception_IO
     */
    public function setWorkingDir($strWorkingDir)
    {
        Xinc_Logger::getInstance()->verbose('Setting workingdir: ' . $strWorkingDir);
        $this->workingDir = $this->checkDirectory($strWorkingDir);
    }

    /**
     * Set the directory in which to project files lies.
     *
     * @param string $strProjectDir Directory of the project files.
     *
     * @throws Xinc_Exception_IO
     */
    public function setProjectDir($strProjectDir)
    {
        Xinc_Logger::getInstance()->verbose('Setting projectdir: ' . $strProjectDir);
        $this->projectDir = $this->checkDirectory($strProjectDir);
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
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->workingDir;
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
        self::$systemTimezone = Xinc_Timezone::get();
        try {
            /**
             * Set up the logging
             */
            $logger = Xinc_Logger::getInstance();
            $arguments = Xinc::handleArguments($args);
            $logger->setLogLevel($arguments['logLevel']);
            $logger->setXincLogFile($arguments['logFile']);
            $logger->info('Starting up Xinc');
            $logger->info('- Version: ' . self::getVersion());
            $logger->info('- Workingdir:         ' . $arguments['workingDir']);
            $logger->info('- Projectdir:         ' . $arguments['projectDir']);
            $logger->info('- Statusdir:          ' . $arguments['statusDir']);
            $logger->info('- System Config File: ' . $arguments['configFile']);
            $logger->info('- Log Level:          ' . $logger->getLogLevel());
            $logger->info('- Daemon:             ' . ($arguments['daemon'] ? 'yes' : 'no'));
            $logger->info('- PID File:           ' . $arguments['pidFile']);
            self::$instance = new Xinc();

            self::$instance->setWorkingDir($arguments['workingDir']);

            self::$instance->setProjectDir($arguments['projectDir']);

            self::$instance->setStatusDir($arguments['statusDir']);

            self::$instance->setPidFile($arguments['pidFile']);

            self::$instance->setSystemConfigFile($arguments['configFile']);

            // get the project config files
            if (isset($arguments['projectFiles'])) {
                /**
                 * pre-process projectFiles
                 */
                $merge = array();
                for ($i = 0; $i < count($arguments['projectFiles']); $i++) {
                    $projectFile = $arguments['projectFiles'][$i];
                    if (!file_exists($projectFile) && strstr($projectFile, '*')) {
                        // we are probably under windows and the command line does not
                        // autoexpand *.xml
                        $array = glob($projectFile);
                        /**
                         * get rid of the not expanded file
                         */
                        unset($arguments['projectFiles'][$i]);
                        /**
                         * merge the glob'ed files
                         */
                        $merge = array_merge($merge, $array);
                    } else {
                        $arguments['projectFiles'][$i] = realpath($projectFile);
                    }
                }
                /**
                 * merge all the autoglobbed files with the original ones
                 */
                $arguments['projectFiles'] = array_merge($arguments['projectFiles'], $merge);

                foreach ($arguments['projectFiles'] as $projectFile) {
                    $logger->info('Loading Project-File: ' . $projectFile);
                    self::$instance->addProjectFile($projectFile);
                }
            }
            self::$instance->start($arguments['daemon']);
        } catch (Xinc_Config_Exception_Getopt $e) {
            $logger->error('Handling Arguments: ' . $e->getMessage(), STDERR);
        } catch (Xinc_Build_Status_Exception_NoDirectory $statusNoDir) {
            $logger->error(
                'Xinc stopped: ' . 'Status Dir: "'
                . $statusNoDir->getDirectory() . '" is not a directory',
                STDERR
            );
        } catch (Xinc_Exception_IO $ioException) {
            $logger->error(
                'Xinc stopped: ' . $ioException->getMessage(),
                STDERR
            );
        } catch (Xinc_Config_Exception_FileNotFound $configFileNotFound) {
            $logger->error(
                'Xinc stopped: ' . 'Config File "'
                . $configFileNotFound->getFileName() . '" not found',
                STDERR
            );
        } catch (Exception $e) {
            // we need to catch everything here
            $logger->error(
                'Xinc stopped due to an uncaught exception: '
                . $e->getMessage() . ' in File : ' . $e->getFile() . ' on line '
                . $e->getLine() . $e->getTraceAsString(),
                STDERR
            );
        }

        self::$instance->shutDown();
    }

    /**
     * Handles command line arguments.
     *
     * @return array The array of parsed arguments.
     * @throws Xinc_Config_Exception_GetOpt
     */
    public static function handleArguments($commandLine = null)
    {
        /**
         * setting default values
         */
        $workingDir = dirname($_SERVER['argv'][0]);
        $arguments = array(
            'configFile'   => $workingDir . DIRECTORY_SEPARATOR . 'system.xml',
            'logLevel'     => Xinc_Logger::DEFAULT_LOG_LEVEL,
            'logFile'      => $workingDir . DIRECTORY_SEPARATOR . 'xinc.log',
            'workingDir'   => $workingDir,
            'projectDir'   => $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_PROJECT_DIR . DIRECTORY_SEPARATOR,
            'statusDir'    => $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_STATUS_DIR . DIRECTORY_SEPARATOR,
        );

        //echo 'Determined options: ' . var_export($options, true) . "\n";
        if (isset($options[1])) {
            $arguments['projectFiles'] = $options[1];
        } else {
            $arguments['projectFiles'] = array();
        }
        /**
         * If no arguments are provided just show help and exit
         * since we at least need a project file to run
         */
        if (count($options[0]) == 0) {
            self::showHelp();
            exit;
        }
        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--config-file':
                case 'f':
                    $arguments['configFile'] = $option[1];
                    break;
                case '--project-dir':
                case 'p':
                    $arguments['projectDir'] = $option[1];
                    break;
                case '--working-dir':
                case 'w':
                    $arguments['workingDir'] = $option[1];
                    break;
                case '--log-file':
                case 'l':
                    $arguments['logFile'] = $option[1];
                    break;

                case '--status-dir':
                case 's':
                    $arguments['statusDir'] = $option[1];
                    break;
                case '--verbose':
                case 'v':
                    $arguments['logLevel'] = $option[1];
                    break;
            }
        }
        // Do some arguments.
        return $arguments;
    }

    /**
     * Add a projectfile to the xinc processing
     *
     * @param string $fileName
     */
    private function addProjectFile($fileName)
    {
        try {
            $config = new Xinc_Project_Config($fileName);
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
     * Checks if a special shutdown file exists
     * and exits if it does
     *
     */
    public function checkShutdown()
    {
        $file = $this->getStatusDir() . DIRECTORY_SEPARATOR . '.shutdown';
        if (file_exists($file) && $this->buildActive == false) {
            Xinc_Logger::getInstance()->info('Preparing to shutdown');
            $statInfo = stat($file);
            $fileUid = $statInfo['uid'];
            /**
             * Only the user running xinc cann issue a shutdown
             */
            if ($fileUid == getmyuid()) {
                $this->shutDown(true);
            } else {
                // delete the file
                unlink($file);
            }
        }
    }

    /**
     * prints help message, describing different parameters to run xinc
     *
     */
    protected function show_help()
    {
        echo 'Usage: xinc [switches] [project-file-1 [project-file-2 ...]]' . "\n\n";

        echo '  -f --config-file=<file>   The config file to use.' . "\n"
            . '  -p --project-dir=<dir>    The project directory.' . "\n"
            . '  -w --working-dir=<dir>    The working directory.' . "\n"
            . '  -l --log-file=<file>      The log file to use.' . "\n"
            . '  -v --verbose=<level>      The level of information to log (default 2).' . "\n"
            . '  -s --status-dir=<dir>     The status directory to use.' . "\n"
            . '  -o --once                 Run once and exit.' . "\n"
            . '  -d --daemon               Daemon, detach and run in the background' . "\n"
            . '  -p --pid-file=<file>      The directory to put the PID file' . "\n"
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
