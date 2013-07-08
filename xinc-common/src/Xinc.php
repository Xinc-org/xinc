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

require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/IO.php';
require_once 'Xinc/Exception/MalformedConfig.php';
require_once 'Xinc/Plugin/Parser.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/Config.php';
require_once 'Xinc/Project/Config.php';
require_once 'Xinc/Engine/Repository.php';
require_once 'Xinc/Build/Queue.php';
require_once 'Xinc/Config/Getopt.php';
require_once 'Xinc/Build/Status/Exception/NoDirectory.php';
require_once 'Xinc/Build/Status/Exception/NonWriteable.php';
require_once 'Xinc/Timezone.php';

class Xinc
{
    const VERSION='@VERSION@';

    const DEFAULT_PROJECT_DIR = 'projects';
    const DEFAULT_STATUS_DIR = 'status';

    /**
     * Registry holds all the projects that the
     * Xinc instance is currently holding
     *
     * @var Xinc_Project_Registry
     */
    private static $_projectRegistry;

    /**
     * Registry holds all configured Xinc Engines
     *
     * @var Xinc_Engine_Registry
     */
    private static $_engineRegistry;

    private $_defaultSleep = 30;

    public $buildActive=false;

    /**
     * Registry holding all scheduled builds
     *
     * @var Xinc_Build_Queue_Interface
     */
    private static $_buildQueue;


    /**
     * parses the generic <configuration/>
     * element of each xinc config file
     * and sets the overriding configuratio
     * for the specific engine
     *
     * @var Xinc_Config_Parser
     */
    private $_configParser;

    private static $_currentBuild;

    private $_pluginParser;
    private $_engineParser;

    /**
     * Current working directory
     * containing the default xinc projects
     *
     * @var string
     */
    private $_workingDir;

    /**
     * Directory holding the projects
     *
     * @var string
     */
    private $_projectDir;

    /**
     *
     * @var Xinc
     */
    private static $_instance;


    private static $_systemTimezone;

    /**
     * Short command line arguments.
     * @var string
     */
    private static $_shortOptions = 'f:p:w:l:s:v:h:o';

    /**
     * Long command line arguments.
     * @var array
     */
    private static $_longOptions = array(
                                         'config-file=',
                                         'project-dir=',
                                         'working-dir=',
                                         'log-file=',
                                         'status-dir=',
                                         'verbose=',
                                         'help',
                                         'version',
                                         'once',
                                         'pid-file='
                                    );



    /**
     * The directory to drop xml status files
     * @var string
     */
    private $_statusDir;

    private $_pidFile;

    /**
     * Holding the config settings
     *
     * @var array
     */
    private $_config = array();

    /**
     * Constructor
     */
    private function __construct()
    {
        //self::$_instance = &$this;
        self::$_buildQueue = new Xinc_Build_Queue();
    }

    /**
     * @return Xinc
     */
    public static function getInstance()
    {
        //if (!isset(self::$_instance)) {
        //    self::main();
        //}

        return self::$_instance;
    }

    public function getSystemTimezone()
    {
        return self::$_systemTimezone;
    }

    /**
     * Specify a config file to be parsed for project definitions.
     *
     * @param string $fileName
     * @throws Xinc_Exception_MalformedConfig
     */
    function setSystemConfigFile($fileName)
    {
        $fileName = realpath($fileName);
        $configFile = Xinc_Config_File::load($fileName);

        $this->_configParser = new Xinc_Config_Parser($configFile);

        $plugins = $this->_configParser->getPlugins();

        $this->_pluginParser = new Xinc_Plugin_Parser();

        $this->_pluginParser->parse($plugins);

        $engines = $this->_configParser->getEngines();
        $this->_engineParser = new Xinc_Engine_Parser();

        $this->_engineParser->parse($engines);

        $configSettings = $this->_configParser->getConfigSettings();
        while ($configSettings->hasNext()) {
            $setting = $configSettings->next();
            $attributes = $setting->attributes();
            $name = (string)$attributes->name;
            $value = (string)$attributes->value;
            if ($name == 'loglevel' && Xinc_Logger::getInstance()->logLevelSet()) {
                $value = Xinc_Logger::getInstance()->getLogLevel();
            }
            self::getInstance()->_setConfigDirective($name, $value);
        }
    }


    private function _setConfigDirective($name, $value)
    {
        $this->_config[$name] = $value;
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
        foreach ($this->_config as $name => $value) {
            $this->_setConfigDirective($name, $value);
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
        return isset($this->_config[$name])?$this->_config[$name]:null;
    }

    public function setPidFile($pidFile)
    {
        $this->_pidFile = $pidFile;
    }

    /**
     * Set the directory in which to save project status files
     *
     * @param string $strStatusDir Directory for the status files.
     *
     * @throws Xinc_Exception_IO
     */
    function setStatusDir($strStatusDir)
    {
        Xinc_Logger::getInstance()->verbose('Setting statusdir: ' . $strStatusDir);
        $this->_statusDir = $this->checkDirectory($strStatusDir);
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
                'Directory "' . $strDirectory .'" does not exist. Trying to create'
            );
            $bCreated = @mkdir($strDirectory, 0755, true);
            if (!$bCreated) {
                $arError = error_get_last();
                Xinc_Logger::getInstance()->verbose(
                    'Directory "' . $strDirectory .'" could not be created.'
                );
                throw new Xinc_Exception_IO(
                    $strDirectory, null, $arError['message']
                );
            }
        } elseif (!is_writeable($strDirectory)) {
            Xinc_Logger::getInstance()->verbose(
                'Directory "' . $strDirectory .'" is not writeable.'
            );
            throw new Xinc_Exception_IO(
                $strDirectory, null, null, Xinc_Exception_IO::FAILURE_NOT_WRITEABLE
            );
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
        return $this->_statusDir;
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
        $nextBuildTime = Xinc::$_buildQueue->getNextBuildTime();

        while (($nextBuild = Xinc::$_buildQueue->getNextBuild()) !== null) {
            $nextBuild->build();
        }
    }

    private function _isProcessRunning($pid)
    {
        if (isset($_SERVER['SystemRoot']) && DIRECTORY_SEPARATOR != '/') {
            /**
             * winserv is handling that
             */
            return false;
        } else {
            exec('ps --no-heading -p ' . $pid, $out, $res);
            if ($res!=0) {
                return false;
            } else {
                if (count($out)>0) {
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
        if (file_exists($this->_pidFile)) {
            $oldPid = file_get_contents($this->_pidFile);
            if ($this->_isProcessRunning($oldPid)) {
                Xinc_Logger::getInstance()->error('Xinc Instance with PID '.$pid.' still running. Check pidfile '.$this->_pidFile.'. Shutting down.');
                exit(-1);
            } else {
                Xinc_Logger::getInstance()->error('Cleaning up old pidFile.');
            }
        }
        file_put_contents($this->_pidFile, getmypid());
        while (true) {
            declare(ticks=2);
            $now = time();
            $nextBuildTime = Xinc::$_buildQueue->getNextBuildTime();
            Xinc_Timezone::reset();
            Xinc_Logger::getInstance()->info('Next buildtime: ' . date('Y-m-d H:i:s', $nextBuildTime));

            if ($nextBuildTime != null) {
                $sleep = $nextBuildTime - $now;
            } else {
                $sleep = $this->_defaultSleep;
            }
            if ($sleep > 0) {
                $this->buildActive=false;
                Xinc_Logger::getInstance()->info('Sleeping: ' . $sleep . ' seconds');
                $start = time() + microtime(true);
                while(((time()+microtime(true)) - $start)<=$sleep) {
                    usleep(10000);
                    /**
                     * Check for forceonly builds here
                     */
                }
            }
            while (($nextBuild = Xinc::$_buildQueue->getNextBuild()) !== null) {
                $this->buildActive=true;
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
        $this->_workingDir = $this->checkDirectory($strWorkingDir);
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
        $this->_projectDir = $this->checkDirectory($strProjectDir);
    }

    /**
     *
     * @return string
     */
    public function getProjectDir()
    {
        return $this->_projectDir;
    }

    /**
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->_workingDir;
    }

    public function getShortOptions()
    {
        return self::$_shortOptions;
    }

    /**
     * Returns Long options of xinc parameters
     *
     * @return array
     */
    public function getLongOptions()
    {
        return self::$_longOptions;
    }

    /**
     * Starts the continuous loop.
     */
    protected function start($daemon)
    {
        if ($daemon) {
            $res=register_tick_function(array(&$this, 'checkShutdown'));
            Xinc_Logger::getInstance()->info('Registering shutdown function: ' . ($res?'OK':'NOK'));
            $this->processBuildsDaemon();
        } else {
            Xinc_Logger::getInstance()->info('Run-once mode '
                                            . '(project interval is negative)');
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
        self::$_systemTimezone = Xinc_Timezone::get();
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
            self::$_instance = new Xinc();

            self::$_instance->setWorkingDir($arguments['workingDir']);

            self::$_instance->setProjectDir($arguments['projectDir']);

            self::$_instance->setStatusDir($arguments['statusDir']);

            self::$_instance->setPidFile($arguments['pidFile']);

            self::$_instance->setSystemConfigFile($arguments['configFile']);

            // get the project config files
            if (isset($arguments['projectFiles'])) {
                /**
                 * pre-process projectFiles
                 */
                $merge = array();
                for ($i = 0; $i<count($arguments['projectFiles']); $i++) {
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
                    self::$_instance->_addProjectFile($projectFile);
                }
            }
            self::$_instance->start($arguments['daemon']);
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

        self::$_instance->shutDown();
    }

    /**
     * Handles command line arguments.
     *
     * @return array The array of parsed arguments.
     * @throws Xinc_Config_Exception_GetOpt
     */
    public static function handleArguments($commandLine = null) 
    {
        if ($commandLine != null) {
            if (!is_array($commandLine) && is_string($commandLine)) {
                $waitForDelimiter = null;
                $validDelimiters = array('"', '"');
                $argument = '';
                $newArgument = false;
                $args = array();
                $commandLine = trim($commandLine);
                for ($i = 0; $i < strlen($commandLine); $i++) {
                    if ($waitForDelimiter != null) {
                        if ($commandLine{$i} == $waitForDelimiter && $commandLine{$i-1} != '\\') {
                            $newArgument = true;
                            $waitForDelimiter = false;
                            continue;
                        }
                    } else if ($commandLine{$i} == ' ' && $commandLine{$i+1} == ' ') {
                        // skip multiple spaces
                        continue;
                    } else if ($commandLine{$i} == ' ' &&
                               $commandLine{$i-1} != '\\' &&
                               !in_array($commandLine{$i+1}, $validDelimiters)) {
                        // Allow \ for escaping of spaces in path names
                        $newArgument = true;
                    } else if ($commandLine{$i} == ' ' &&
                               $commandLine{$i-1} != '\\' &&
                               in_array($commandLine{$i+1}, $validDelimiters)) {
                        $newArgument = true;
                        $waitForDelimiter = $commandLine{$i+1};
                        // move ahead, since we dont want the delimiter to be part of the param
                        $i++;
                    } else if ($i + 1 >= strlen($commandLine)) {
                        $argument .= $commandLine{$i};
                        $newArgument = true;
                    }
                    if ($newArgument) {
                        $args[] = $argument;
                        $newArgument = false;
                        $argument = '';
                    } else {
                        $argument .= $commandLine{$i};
                    }
                }
                $commandLine = $args;
            }
        } else {
            $commandLine = $_SERVER['argv']; 
        }
        /**
         * setting default values
         */
        $workingDir = dirname($_SERVER['argv'][0]);
        $arguments = array('daemon'       => true,
                           'configFile'   => $workingDir . DIRECTORY_SEPARATOR . 'system.xml',
                           'logLevel'     => Xinc_Logger::DEFAULT_LOG_LEVEL,
                           'logFile'      => $workingDir . DIRECTORY_SEPARATOR . 'xinc.log',
                           'workingDir'   => $workingDir,
                           'projectDir'   => $workingDir . DIRECTORY_SEPARATOR
                                             . self::DEFAULT_PROJECT_DIR . DIRECTORY_SEPARATOR,
                           'statusDir'    => $workingDir . DIRECTORY_SEPARATOR
                                             . self::DEFAULT_STATUS_DIR . DIRECTORY_SEPARATOR);
      
        $options = Xinc_Config_Getopt::getopt($commandLine, self::$_shortOptions, self::$_longOptions);
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
                case '--once':
                case 'o':
                    $arguments['daemon'] = false;
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
                case '--pid-file':
                    $arguments['pidFile'] = $option[1];
                    break;
                case '--version':
                    self::printVersion();
                    exit;
                    break;
                case '--help': 
                    self::showHelp();
                    exit;
            }
        }
        if (!isset($arguments['pidFile'])) {
            $arguments['pidFile'] = $arguments['statusDir'] . DIRECTORY_SEPARATOR . 'xinc.pid';
        }
        // Do some arguments.
        return $arguments;
    }

    /**
     * Add a projectfile to the xinc processing
     *
     * @param string $fileName
     */
    private function _addProjectFile($fileName)
    {
        try {
            $config = new Xinc_Project_Config($fileName);
            $engineName = $config->getEngineName();

            $engine = Xinc_Engine_Repository::getInstance()->getEngine($engineName);

            $builds = $engine->parseProjects($config->getProjects());

            Xinc::$_buildQueue->addBuilds($builds);

        } catch (Xinc_Project_Config_Exception_FileNotFound $notFound) {
            Xinc_Logger::getInstance()->error('Project Config File ' . $fileName . ' cannot be found');
        } catch (Xinc_Project_Config_Exception_InvalidEntry $invalid) {
            Xinc_Logger::getInstance()->error('Project Config File has an invalid entry: ' . $invalid->getMessage());
        } catch (Xinc_Engine_Exception_NotFound $engineNotFound) {
            Xinc_Logger::getInstance()->error('Project Config File references an unknown Engine: ' 
                                             . $engineNotFound->getMessage());
        }
    }

    public static function &getCurrentBuild()
    {
        return self::$_currentBuild;
    }

    /**
     * Sets the build that is currently being processed
     *
     * @param Xinc_Build_Interface $build
     */
    public static function setCurrentBuild(Xinc_Build_Interface &$build)
    {
        self::$_currentBuild = $build;
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
     * shutsdown the xinc instance and cleans up pidfile etc
     *
     * @param boolean $exit
     */
    private function shutDown($exit=false)
    {
        $file = $this->getStatusDir() . DIRECTORY_SEPARATOR . '.shutdown';
        if (file_exists($file)) {
            unlink($file);
        }
        $pidFile = $this->_pidFile;
        if (file_exists($pidFile)) {
                unlink($pidFile);
        }
        Xinc_Logger::getInstance()->info('Goodbye. Shutting down Xinc');
        if ($exit) {
            exit();
        }
    }

    /**
     * prints help message, describing different parameters to run xinc
     *
     */
    public static function showHelp()
    {
        echo "Usage: xinc [switches] [project-file-1 [project-file-2 ...]]\n\n";

        echo "  -f --config-file=<file>   The config file to use.\n" .
             "  -p --project-dir=<dir>    The project directory.\n" .
             "  -w --working-dir=<dir>    The working directory.\n" .
             "  -l --log-file=<file>      The log file to use.\n" . 
             "  -v --verbose=<level>      The level of information to log (default 2).\n" . 
             "  -s --status-dir=<dir>     The status directory to use.\n" . 
             "  -o --once                 Run once and exit.\n" .
             "  --pid-file=<file>         The directory to put the PID file" .
             "  --version                 Prints the version of Xinc.\n" .
             "  -h --help                 Prints this help message.\n";
    }

    /**
     * Prints the version of xinc
     *
     */
    public static function printVersion()
    {
        echo "Xinc version " . self::getVersion() . "\n";
    }

    /**
     * Returns the Version of Xinc
     *
     * @return string
     */
    public static function getVersion()
    {
        return self::VERSION;
    }
}
