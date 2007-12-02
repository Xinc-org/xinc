<?php
/**
 * The main control class.
 *
 * @package Xinc
 * @author Arno Schneider
 * @author David Ellis
 * @author Gavin Foster
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
require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/MalformedConfig.php';
require_once 'Xinc/Plugin/Parser.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/Config.php';
require_once 'Xinc/Project/Config.php';
require_once 'Xinc/Engine/Repository.php';
require_once 'Xinc/Build/Queue.php';

class Xinc
{
    
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

    
    private static $_instance;






    /**
     * The directory to drop xml status files
     * @var string
     */
    private $_statusDir;


    /**
     * Constructor.
     */
    private function __construct() {
        self::$_instance = &$this;
        self::$_buildQueue = new Xinc_Build_Queue();
     
        
    }
    
    
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::main();
        }
        
        return self::$_instance;
    }

    /**
     * Specify a config file to be parsed for project definitions.
     *
     * @param string $fileName
     * @throws Xinc_Exception_MalformedConfig
     */
    function setSystemConfigFile($fileName)
    {
        
        try {
            $configFile = Xinc_Config_File::load($fileName);
            
            $this->_configParser = new Xinc_Config_Parser($configFile);
            
            $plugins = $this->_configParser->getPlugins();
            
            $this->_pluginParser = new Xinc_Plugin_Parser();
            
            $this->_pluginParser->parse($plugins);
            
            $engines = $this->_configParser->getEngines();
            $this->_engineParser = new Xinc_Engine_Parser();
            
            $this->_engineParser->parse($engines);
            
            
        } catch(Exception $e) {
            Xinc_Logger::getInstance()->error($e->getMessage());
            throw new Xinc_Exception_MalformedConfig($e->getMessage());
        }
    }


    /**
     * Set the directory in which to save project status files
     *
     * @param string $statusDir
     */
    function setStatusDir($statusDir)
    {
        $this->_statusDir = $statusDir;
    }

    /**
     *
     * @return String
     */
    public function getStatusDir(){
        return $this->_statusDir;
    }


   
    /**

    /**
    * Processes the projects that have been configured 
    * in the config-file and executes each project
    * if the scheduled time has expired
    *
    */
    public function processBuildsRunOnce(){

        while (($nextBuild = Xinc::$_buildQueue->getNextBuild()) !== null) {
            
            $nextBuild->build();
           
        }
        
    }

    /**
    * Processes the projects that have been configured 
    * in the config-file and executes each project
    * if the scheduled time has expired
    *
    */
    public function processBuildsDaemon(){
        while (true) {
            $now = time();
            $nextBuildTime = Xinc::$_buildQueue->getNextBuildTime();
            
            Xinc_Logger::getInstance()->info('Next buildtime: ' . date('Y-m-d H:i:s', $nextBuildTime));
            
            if ($nextBuildTime != null) {
            
                $sleep = $nextBuildTime - $now;
            } else {
                $sleep = $this->_defaultSleep;
            }
            if ($sleep > 0) {
                Xinc_Logger::getInstance()->info('Sleeping: ' . $sleep . ' seconds');
                for ($i=0; $i<$sleep*100; $i++) {
                    usleep(10000);
                }
                //sleep($sleep);
            }
            while (($nextBuild = Xinc::$_buildQueue->getNextBuild()) !== null) {
                
                $nextBuild->build();
               
            }
        }
    }
    
    public function setWorkingDir($dir)
    {
        $this->_workingDir = $dir;
    }
    
    public function setProjectDir($dir)
    {
        $this->_projectDir = $dir;
    }
    
    public function getProjectDir()
    {
        return $this->_projectDir;
    }
    
    public function getWorkingDir()
    {
        return $this->_workingDir;
    }
    /**
     * Starts the continuous loop.
     */
    protected function start($daemon)
    {
        
        if ($daemon) {
            declare(ticks=2);
            register_tick_function(array(&$this, 'checkShutdown'));
            register_shutdown_function(array(&$this,'shutdown'));
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
     * @param string $workingDir pointing to the base working directory
     * @param string $projectDir pointing to the directory where all the project data is
     * @param string $statusDir directory pointing to the build-statuses for the projects
     * @param string $systemConfigFile the system.xml file 
     * @param string $logFile daemon log file
     * @param integer $logLevel verbosity of the logging
     * @param boolean $daemon determins if we are running as daemon or in run-once mode
     * @param string $configFile1
     * @param string $configFile2 ...
     */
    public static function main($workingDir = null,
                                $projectDir = null,
                                $statusDir = null,
                                $systemConfigFile = null,
                                $logFile = null,
                                $logLevel = 0,
                                $daemon = true)
    {
        /**
         * See Issue 57.
         * Will be substituted by configuration option
         */
        $defaultTimeZone = ini_get('date.timezone');
        if (empty($defaultTimeZone)) {
            /**
             * Go for the safer version. date_default_timezone_* needs php >=5.1.0
             */
            ini_set('date.timezone', 'UTC');
        }
        
        if ($workingDir == null) {
            $workingDir = dirname($_SERVER['argv'][0]);
        }
        /**
         * Set up the logging
         */
        $logger = Xinc_Logger::getInstance();
        
        $logger->setLogLevel($logLevel);
        if ($logFile == null) {
            $logFile = $workingDir . DIRECTORY_SEPARATOR . 'xinc.log';
            
        }
        $logger->setXincLogFile($logFile);
        
        $logger->info('Starting up Xinc');
        
        
        if ($projectDir == null) {
            $projectDir = $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_PROJECT_DIR . DIRECTORY_SEPARATOR;
        }
        if ($statusDir == null) {
            $statusDir = $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_STATUS_DIR . DIRECTORY_SEPARATOR;
        }
        
        if ($systemConfigFile == null) {
            $systemConfigFile = $workingDir . DIRECTORY_SEPARATOR . 'system.xml';
        }
        
        if ($logFile == null) {
            $logFile = $workingDir . DIRECTORY_SEPARATOR . 'xinc.log';
        }
        $logger->info('- Workingdir:         ' . $workingDir);
        $logger->info('- Projectdir:         ' . $projectDir);
        $logger->info('- Statusdir:          ' . $statusDir);
        $logger->info('- System Config File: ' . $systemConfigFile);
        $logger->info('- Log Level:          ' . $logLevel);
        $logger->info('- Daemon:             ' . ($daemon==true ? 'yes':'no'));
        self::$_instance = new Xinc();
        try {
            self::$_instance->setWorkingDir($workingDir);

            self::$_instance->setProjectDir($projectDir);

            self::$_instance->setStatusDir($statusDir);

            
            self::$_instance->setSystemConfigFile($systemConfigFile);
            
            // get the project config files
            if (func_num_args() > 7) {
                
                for ($i = 7; $i < func_num_args(); $i++) {
                    $logger->info('Loading Project-File: ' . func_get_arg($i));
                    self::$_instance->_addProjectFile(func_get_arg($i));
                }
            }
            
            self::$_instance->start($daemon);
        } catch (Exception $e) {
            // we need to catch everything here
            $logger->error('Xinc stopped due to an uncaught exception: ' 
                          . $e->getMessage() . ' in File : ' . $e->getFile() . ' on line ' . $e->getLine() 
                          . $e->getTraceAsString());
        }
    }
    
    private function _addProjectFile($fileName)
    {
        
        
        try {
            
            file_put_contents($this->getStatusDir() . DIRECTORY_SEPARATOR . 'xinc.pid', getmypid());
            
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
    public static function setCurrentBuild(Xinc_Build_Interface &$build)
    {
        self::$_currentBuild = $build;
    }
    
    public function getBuiltinProperties()
    {
        $properties = array();
        $properties['xinc.workingdir'] = $this->getWorkingDir();
        $properties['xinc.statusdir'] = $this->getStatusDir();
        $properties['xinc.projectdir'] = $this->getProjectDir();
        
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
        if (file_exists($file)) {
            Xinc_Logger::getInstance()->info('Preparing to shutdown');
            $statInfo = stat($file);
            Xinc_Logger::getInstance()->info('info:' . var_export($statInfo,true));
            $fileUid = $statInfo['uid'];
            if ($fileUid == getmyuid()) {
                unlink($file);
                exit();
            }
        }
    }
    
    public function shutdown()
    {
        unlink($this->getStatusDir() . DIRECTORY_SEPARATOR . 'xinc.pid');
    }
}
