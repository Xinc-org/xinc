<?php
/**
 * The main control class.
 *
 * @package Xinc
 * @author David Ellis
 * @author Gavin Foster
 * @version 1.0
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
require_once 'Xinc/Parser.php';
require_once 'Xinc/Exception/MalformedConfig.php';
require_once 'Xinc/Plugin/Parser.php';



class Xinc
{
    private static $_currentProject;
    
    private $_workingDir;
    
    private static $_instance;
    /**
     * The projects that Xinc is going build.
     *
     * @var Project[]
     */
    private $_projects;



    /**
     * The parser.
     *
     * @var Parser
     */
    private $_parser;

    /**
     * The directory to drop xml status files
     * @var string
     */
    private $_statusDir;

    /**
     * Process queue keeps the projects in the order they have 
     * to be processed
     * format is array ( $project => $timestamp )
     *
     * @var Array
     */
    private $_processQueue = array ();
    /**
     * Constructor.
     */
    function __construct() {
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
        $this->_projects = array();
        $this->_parser = new Xinc_Parser();
        $this->pluginParser = new Xinc_Plugin_Parser();
        self::$_instance = &$this;
     
        
    }
    
    
    public static function getInstance(){
        return self::$_instance;
    }
    public static function getCurrentProject(){
        return self::$_currentProject;
    }
    /**
     * Specify a config file to be parsed for project definitions.
     *
     * @param string $fileName
     * @throws Xinc_Exception_MalformedConfig
     */
    function setConfigFile($fileName)
    {
        try {
            $this->_projects = $this->_parser->parse($fileName);
            /**
             * init the project-status files in the status dir
             */
            foreach ($this->_projects as $project) {
                $projectDir = $this->_statusDir . DIRECTORY_SEPARATOR . $project->getName();
                $xincProjectFile = $this->_statusDir . DIRECTORY_SEPARATOR . $project->getName() . DIRECTORY_SEPARATOR . '.xinc';
                
                Xinc_Logger::getInstance()->info('Creating project-file '.$xincProjectFile);
                if (!file_exists($projectDir)) {
                    mkdir($projectDir);
                }
                if (!file_exists($xincProjectFile)) {
                    touch($xincProjectFile);
                }
                /**
                 * for windows compatibility use phps functions
                 */
                //exec_('mkdir '.$projectDir.';touch '.$xincProjectFile);
            }
        } catch(Exception $e) {
            Xinc_Logger::getInstance()->error($e->getMessage());
            throw new Xinc_Exception_MalformedConfig();
        }
    }
    function setPluginConfigFile($fileName)
    {
        try {
            $this->pluginParser->parse($fileName);
        } catch(Exception $e) {
            Xinc_Logger::getInstance()->error("error parsing plugin-tasks:"
                                             . $e->getMessage());
                
        }
    }
    /**
     * Specify multiple config files to be parsed for project definitions.
     *
     * @param string[] $fileNames
     */
    function setConfigFiles($fileNames)
    {
        foreach ($fileNames as $fileName) {
            $this->setConfigFile($fileName);
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

    public function getStatusDir(){
        return $this->_statusDir;
    }
    /**
     * Set the projects to build.
     *
     * @param Project[] $projects
     */
    function setProjects($projects)
    {
        $this->_projects = $projects;
    }

    /**
     * Adds the passed in project
     *
     * @param Project $project
     */
    function addProject($project)
    {
        $this->_projects[] = $project;
    }

    /**
     * Gets the projects being built
     *
     * @return Project[] $projects
     */
    function getProjects()
    {
        return $this->_projects;
    }


    /**
     * processes a single project
     * @param Project $project
     */
    function processProject(Xinc_Project &$project)
    {
        self::$_currentProject=$project;
        //if (time() < $project->getSchedule() || $project->getSchedule() == null ) return;
        //if (time() < $project->getSchedule() ) return;

        
        $buildTime = time();
        /**
         * By default a project is not processed, unless
         * a modification set sets it to PASSED
         */
        //$project->setStatus(Xinc_Project_Build_Status_Interface::STOPPED);
        $project->process(Xinc_Plugin_Slot::INIT_PROCESS);
        if ( Xinc_Project_Build_Status_Interface::STOPPED == $project->getStatus() ) {
            Xinc_Logger::getInstance()->info('Build of Project stopped'
                                             . ' in INIT phase');
            //$project->serialize();
            $project->setStatus(Xinc_Project_Build_Status_Interface::INITIAL);
            Xinc_Logger::getInstance()->setBuildLogFile(null);
            Xinc_Logger::getInstance()->flush();
            self::$_currentProject=null;
            return;
        }                                
        Xinc_Logger::getInstance()->info("CHECKING PROJECT " 
                                        . $project->getName());
        $project->process(Xinc_Plugin_Slot::PRE_PROCESS);
        
        if ( Xinc_Project_Build_Status_Interface::STOPPED == $project->getStatus() ) {
            $project->info("Build of Project stopped, "
                                             . "no build necessary");
             //$project->setBuildTime($buildTime);
            $project->setStatus(Xinc_Project_Build_Status_Interface::INITIAL);
            Xinc_Logger::getInstance()->setBuildLogFile(null);
            Xinc_Logger::getInstance()->flush();
            return;
        } else if ( Xinc_Project_Status::FAILED == $project->getStatus() ) {
            $project->error("Build failed");
            /**
             * Process failed in the pre-process phase, we need
             * to run post-process to maybe inform about the failed build
             */
            $project->process(Xinc_Plugin_Slot::POST_PROCESS);
            //$project->reschedule();
            //$project->serialize();
           
        } else if ( Xinc_Project_Status::PASSED == $project->getStatus() ) {

            $project->info("Code not up to date, "
                                            . "building project");
            $project->setBuildTime($buildTime);
            $project->process(Xinc_Plugin_Slot::PROCESS);
            if ( Xinc_Project_Status::PASSED == $project->getStatus() ) {
                $project->info("BUILD PASSED FOR PROJECT " 
                                                . $project->getName());
            } else if ( Xinc_Project_Status::STOPPED == $project->getStatus() ) {
                $project->warn("BUILD STOPPED FOR PROJECT " 
                                                . $project->getName());
            } else {
                $project->error("BUILD FAILED FOR PROJECT " 
                                                . $project->getName());
            }

            $processingPast = $project->getStatus();
            /**
             * Post-Process is run on Successful and Failed Builds
             */
            $project->process(Xinc_Plugin_Slot::POST_PROCESS);
            
            if ( $processingPast == Xinc_Project_Status::PASSED ) {
               
            
                $project->getBuildLabeler()->buildSuccessful();
                $project->getBuildStatus()->buildSuccessful();
                
            } else {
                $project->getBuildLabeler()->buildFailed();
                $project->getBuildStatus()->buildFailed();
            }
            
        }
            //$project->publish();
            //$project->reschedule();
            //$project->serialize();
            $project->setStatus(Xinc_Project_Build_Status_Interface::INITIAL);
            self::$_currentProject=null;
    }

    /**

    /**
    * Processes the projects that have been configured 
    * in the config-file and executes each project
    * if the scheduled time has expired
    *
    */
    function processProjects(){
        foreach ($this->_projects as $project ) {
            $this->processProject($project);
        }
    }

    public function setWorkingDir($dir){
        $this->_workingDir=$dir;
    }
    public function getWorkingDir(){
        return $this->_workingDir;
    }
    /**
     * Starts the continuous loop.
     */
    protected function start($daemon,$minWait=10)
    {
        /** figure out minimum time to wait between checking projects */
        //$minWait = -1;
        /**$processQueue=array();
        foreach ($this->_projects as $project) {
            $this->_processQueue[]=&$project;
        }*/
        
        //usort($this->_processQueue, array(&$this, "orderProcessQueue"));
       
        if ($daemon) {
            while ( true ) {
                Xinc_Logger::getInstance()->debug('Sleeping for ' 
                                                . $minWait . ' seconds');
                //Xinc_Logger::getInstance()->flush();
                sleep((float) $minWait);
                //if ($this->_processQueue[0]->getSchedule() < time() ) {
                //    $this->processProject($this->_processQueue[0]);
               // }
                foreach ($this->_projects as $project ) {
                    if ($project->getSchedule() < time()) {
                        $this->processProject($project);
                    }
                }
                
                //usort($this->_processQueue, array(&$this,"orderProcessQueue"));
            }
        } else {
            Xinc_Logger::getInstance()->info('Run-once mode '
                                            . '(project interval is negative)');
            //Xinc_Logger::getInstance()->flush();
            $this->processProjects();
        }
    }

    /**
     * Sorts the process in the order they
     * need to be processed
     *
     * @param array $a
     * @param array $b
     * @return integer
     */
    public function orderProcessQueue($a,$b){
        if ($a->getSchedule() == $b->getSchedule()) {
            return 0;
        }
        return ($a->getSchedule() < $b->getSchedule()) ? -1 : 1;
    }
    /**
     * Static main function called by bin script
     */
    public static function main($configFile, 
                                $pluginConfigFile, 
                                $logFile, $statusDir, $logLevel=0, $daemon=true)
    {
        $logger = Xinc_Logger::getInstance();
        $logger->setXincLogFile($logFile);
        $logger->setLogLevel($logLevel);
        $xinc = new Xinc();
        $xinc->setWorkingDir(dirname($_SERVER['argv'][0]));
        $xinc->setStatusDir($statusDir);
        $xinc->setPluginConfigFile($pluginConfigFile);
        $xinc->setConfigFile($configFile);
        
        
        
        $xinc->start($daemon);
    }
}
