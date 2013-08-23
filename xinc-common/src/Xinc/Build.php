<?php
/**
 * Xinc - Continuous Integration.
 * This class represents the build that is going to be run with Xinc
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build
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

require_once 'Xinc/Build/Interface.php';
require_once 'Xinc/Build/Properties.php';
require_once 'Xinc/Build/Exception/NotRun.php';
require_once 'Xinc/Build/Exception/NotFound.php';
require_once 'Xinc/Build/Exception/Serialization.php';
require_once 'Xinc/Build/Labeler/Default.php';
require_once 'Xinc/Build/Scheduler/Default.php';
require_once 'Xinc/Project/Status.php';
require_once 'Xinc/Build/Statistics.php';
require_once 'Xinc/Build/History.php';
require_once 'Xinc/Timezone.php';

class Xinc_Build implements Xinc_Build_Interface
{
    /**
     * Are we queued?
     *
     * @var boolean
     */
    private $_isQueued = false;

    /**
     * @var Xinc_Engine_Interface
     */
    private $_engine;

    /**
     * @var Xinc_Project
     */
    private $_project;

    /**
     * @var Xinc_Build_Properties
     */
    private $properties;

    /**
     * @var Xinc_Build_Properties
     */
    private $_internalProperties;

    /**
     * @var Xinc_Build_Statistics
     */
    private $_statistics;

    /**
     * @var integer
     */
    private $_buildTimestamp;

    /**
     * @var integer
     */
    private $_nextBuildTimestamp;

    /**
     * Build status, as defined in Xinc_Build_Interface
     *
     * @var integer
     */
    private $_status;

    /**
     *
     * @var Xinc_Build_Interface
     */
    private $lastBuild;

    /**
     * The build no of this build
     *
     * @var integer
     */
    private $_no;

    /**
     * The label for this build
     *
     * @var string
     */
    private $_label;

    /**
     * Contains tasks that need to be executed for each Process Step
     *
     * @var Xinc_Build_Tasks_Registry
     */
    private $_taskRegistry;

    /**
     * Build schedulers
     *
     * @var array of Xinc_Build_Scheduler_Interface
     */
    private $schedulers;

    /**
     * @var Xinc_Build_Labeler_Interface
     */
    private $_labeler;

    /**
     * Holding config values for this build
     *
     * @var array
     */
    private $_config = array();

    // ONLY FOR Backward Compatibility will be removed later.
    /**
     * @var Xinc_Build_Properties
     */
    private $_properties;

    /**
     * @var Xinc_Build_Interface
     */
    private $_lastBuild;

    /**
     * sets the project, engine
     * and timestamp for the build
     *
     * @param Xinc_Engine_Interface $engine
     * @param Xinc_Project          $project
     * @param integer               $buildTimestamp
     */
    public function __construct(
        Xinc_Engine_Interface &$engine,
        Xinc_Project &$project,
        $buildTimestamp = null
    ) {
        $this->_engine = $engine;
        $this->_project = $project;

        if (Xinc_Project_Status::MISCONFIGURED == $this->_project->getStatus()) {
            $this->setStatus(Xinc_Build_Interface::MISCONFIGURED);
        }

        $this->_buildTimestamp = $buildTimestamp;
        $this->properties = new Xinc_Build_Properties();
        $this->_internalProperties = new Xinc_Build_Properties();
        $this->_statistics = new Xinc_Build_Statistics();
        $this->setLabeler(new Xinc_Build_Labeler_Default());
    }

    public function setLabeler(Xinc_Build_Labeler_Interface &$labeler)
    {
        $this->_labeler = $labeler;
    }

    /**
     * Returns the last build
     *
     * @return Xinc_Build_Interface
     */
    public function getLastBuild()
    {
        if ($this->lastBuild == null) {
            $build = new Xinc_Build($this->getEngine(), $this->getProject());
            return $build;
        }
        return $this->lastBuild;
    }

    /**
     *
     * @return Xinc_Build_Properties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     *
     * @return Xinc_Build_Properties
     */
    public function &getInternalProperties()
    {
        return $this->_internalProperties;
    }

    /**
     * @return Xinc_Build_Statistics
     */
    public function &getStatistics()
    {
        return $this->_statistics;
    }
    /**
     * sets the build time for this build
     *
     * @param integer $buildTime unixtimestamp
     */
    public function setBuildTime($buildTime)
    {
        $this->getProperties()->set('build.timestamp', $buildTime);
        $this->_buildTimestamp = $buildTime;
    }

    /**
     * returns the timestamp of this build
     *
     * @return integer Timestamp of build (unixtimestamp)
     */
    public function getBuildTime()
    {
        return $this->_buildTimestamp;
    }

    /**
     * Returns the next build time (unix timestamp)
     * for this build
     *
     */
    public function getNextBuildTime()
    {
        $timestampMerged = null;

        // @var $scheduler Xinc_Build_Scheduler_Interface
        foreach ($this->schedulers as $scheduler) {
            $timestamp = $scheduler->getNextBuildTime($this);
            if ($timestamp < $timestampMerged || $timestampMerged === null) {
                $timestampMerged = $timestamp;
            }
        }

        return $timestampMerged;
    }

    /**
     *
     * @return Xinc_Project
     */
    public function &getProject()
    {
        return $this->_project;
    }

    /**
     * 
     * @return Xinc_Engine_Interface
     */
    public function &getEngine()
    {
        return $this->_engine;
    }

    public function setLastBuild()
    {
        /**
         * to prevent recursion, unset the reference to the lastBuild
         * and then clone
         */
        $this->lastBuild = null;
        $this->lastBuild = clone $this;
    }

    /**
     * stores the build information
     *
     * @throws Xinc_Build_Exception_NotRun
     * @throws Xinc_Build_Exception_Serialization
     * @throws Xinc_Build_History_Exception_Storage
     * @return boolean
     */
    public function serialize()
    {
        Xinc_Logger::getInstance()->flush();
        $this->setLastBuild();

        if (!in_array($this->getStatus(), array(self::PASSED, self::FAILED, self::STOPPED))) {
            throw new Xinc_Build_Exception_NotRun();
        } elseif ($this->getBuildTime() == null) {
            throw new Xinc_Build_Exception_Serialization($this->getProject(), $this->getBuildTime());
        }
        $statusDir = Xinc::getInstance()->getStatusDir();

        $buildHistoryFile = $statusDir . DIRECTORY_SEPARATOR . $this->getProject()->getName() . '.history';

        $subDirectory = self::generateStatusSubDir($this->getProject()->getName(), $this->getBuildTime());

        $fileName = $statusDir . DIRECTORY_SEPARATOR . $subDirectory . DIRECTORY_SEPARATOR . 'build.ser';
        $logfileName = $statusDir . DIRECTORY_SEPARATOR . $subDirectory . DIRECTORY_SEPARATOR . 'buildlog.xml';
        $lastBuildFileName
            = $statusDir . DIRECTORY_SEPARATOR . $this->getProject()->getName() . DIRECTORY_SEPARATOR . 'build.ser';
        $lastLogFileName
            = $statusDir . DIRECTORY_SEPARATOR . $this->getProject()->getName() . DIRECTORY_SEPARATOR . 'buildlog.xml';
        if (!file_exists(dirname($fileName))) {
            mkdir(dirname($fileName), 0755, true);
        }
        $contents = serialize($this);

        $written = file_put_contents($lastBuildFileName, $contents);
        if ($written == strlen($contents)) {
            $res = copy($lastBuildFileName, $fileName);
            if (!$res) {
                throw new Xinc_Build_Exception_Serialization($this->getProject(), $this->getBuildTime());
            } else {
                if (file_exists($lastLogFileName)) {
                    copy($lastLogFileName, $logfileName);
                    unlink($lastLogFileName);
                }
                Xinc_Build_History::addBuild($this, $fileName);
            }
            return true;
        } else {
            throw new Xinc_Build_Exception_Serialization($this->getProject(), $this->getBuildTime());
        }
    }

    /**
     * Unserialize a build by its project and buildtimestamp
     *
     * @param Xinc_Project $project
     * @param integer $buildTimestamp
     *
     * @return Xinc_Build
     * @throws Xinc_Build_Exception_Unserialization
     * @throws Xinc_Build_Exception_NotFound
     */
    public static function &unserialize(Xinc_Project &$project, $buildTimestamp = null, $statusDir = null)
    {
        if ($statusDir == null) {
            $statusDir = Xinc::getInstance()->getStatusDir();
        }

        if ($buildTimestamp == null) {
            //$fileName = $statusDir . DIRECTORY_SEPARATOR . $project->getName()
            //          . DIRECTORY_SEPARATOR . 'build.ser';
            $fileName = Xinc_Build_History::getLastBuildFile($project);
        } else {
            //$subDirectory = self::generateStatusSubDir($project->getName(), $buildTimestamp);
            /**
             * @throws Xinc_Build_Exception_NotFound
             */
            $fileName = Xinc_Build_History::getBuildFile($project, $buildTimestamp);
        }

        //Xinc_Build_Repository::getBuild($project, $buildTimestamp);
        if (!file_exists($fileName)) {
            throw new Xinc_Build_Exception_NotFound($project, $buildTimestamp);
        } else {
            $serializedString = file_get_contents($fileName);
            $unserialized = @unserialize($serializedString);
            if (!$unserialized instanceof Xinc_Build) {
                throw new Xinc_Build_Exception_Unserialization($project, $buildTimestamp);
            } else {
                /**
                 * compatibility with old Xinc_Build w/o statistics object
                 */
                if ($unserialized->getStatistics() === null) {
                    $unserialized->_statistics = new Xinc_Build_Statistics();
                }
                if ($unserialized->getConfigDirective('timezone.reporting') == true) {
                    $unserialized->setConfigDirective('timezone', null);
                }
                if (!isset($unserialized->_internalProperties)) {
                    if (method_exists($unserialized, 'init')) {
                        $unserialized->init();
                    }
                }
                return $unserialized;
            }
        }
    }

    /**
     * returns the status of this build
     *
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Set the status of this build
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function __wakeup()
    {
        if (isset($this->_properties)) {
            $this->properties = $this->_properties;
        }
        if (isset($this->_lastBuild)) {
            $this->lastBuild = $this->_lastBuild;
        }
    }

    public function __sleep()
    {
        /**
         * minimizing the storage for the project,
         * we just want the name
         */
        $project = new Xinc_Project();
        $project->setName($this->getProject()->getName());
        $this->_project = $project;
        if (!isset($this->_config['timezone'])) {
            /**
             * if no timezone was configured in the project xml, we store the
             * used timezone and mark it as only a reporting timezone,
             * like this the timezone will not get restored for future
             * builds, but we have it for the record
             */
            $this->_config['timezone'] = Xinc_Timezone::get();
            $this->_config['timezone.reporting'] = true;
        }

        return array(
            '_no','_project', '_buildTimestamp', 'properties', '_status', 'lastBuild', '_labeler', '_engine',
            '_statistics', '_config', '_internalProperties'
        );
    }

    public function init()
    {
        $this->_internalProperties = new Xinc_Build_Properties();
    }

    /**
     * Sets the sequence number for this build
     *
     * @param integer $no
     */
    public function setNumber($no)
    {
        $this->info('Setting Buildnumber to:' . $no);
        $this->getProperties()->set('build.number', $no);
        $this->_no = $no;
    }

    /**
     * returns the build no for this build
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->_no;
    }

    /**
     * returns the label of this build
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_labeler->getLabel($this);
    }

    /**
     * returns the labeler of this build
     *
     * @return Xinc_Build_Labeler
     */
    public function getLabeler()
    {
        return $this->_labeler;
    }

    /**
     *
     * @param Xinc_Build_Tasks_Registry $taskRegistry
     */
    public function setTaskRegistry(Xinc_Build_Tasks_Registry $taskRegistry)
    {
        $this->_taskRegistry = $taskRegistry;
    }

    /**
     * Sets a build scheduler,
     * which calculates the next build time based
     * on the configuration
     *
     * @param Xinc_Build_Scheduler_Interface $scheduler
     */
    public function addScheduler(Xinc_Build_Scheduler_Interface $scheduler)
    {
        $this->schedulers[] = $scheduler;
    }

    /**
     * Returns the availability of at least one scheduler.
     *
     * @return boolean True if a minimum of one scheduler is set.
     */
    public function haveScheduler()
    {
        return (count($this->schedulers) > 0);
    }

    /**
     * @return Xinc_Build_Tasks_Registry
     *
     */
    public function getTaskRegistry()
    {
        return $this->_taskRegistry;
    }

    /**
     * processes the tasks that are registered for the slot
     *
     * @param mixed $slot
     */
    public function process($slot)
    {
        $tasks = $this->getTaskRegistry()->getTasksForSlot($slot);
        while ($tasks->hasNext()) {
            $task = $tasks->next();
            Xinc_Logger::getInstance()->info('Processing task: ' . $task->getName());
            try {
                $task->process($this);
            } catch (Exception $e) {
                var_dump($e);
            }

            /**
             * The Post-Process continues on failure
             */
            if ($slot != Xinc_Plugin_Slot::POST_PROCESS) {
                if ($this->getStatus() != Xinc_Build_Interface::PASSED) {
                    $tasks->rewind();
                    break;
                }
            }
        }
        $tasks->rewind();
    }

    /**
     * Logs a message of priority info
     *
     * @param string $message
     */
    public function info($message)
    {
        Xinc_Logger::getInstance()->info('[build] ' . $this->getProject()->getName() . ': ' . $message);
    }

    /**
     * Logs a message of priority warn
     *
     * @param string $message
     */
    public function warn($message)
    {
        Xinc_Logger::getInstance()->warn('[build] ' . $this->getProject()->getName() . ': ' . $message);
    }

    /**
     * Logs a message of priority verbose
     *
     * @param string $message
     */
    public function verbose($message)
    {
        Xinc_Logger::getInstance()->verbose('[build] ' . $this->getProject()->getName() . ': ' . $message);
    }

    /**
     * Logs a message of priority debug
     *
     * @param string $message
     */
    public function debug($message)
    {
        Xinc_Logger::getInstance()->debug('[build] ' . $this->getProject()->getName() . ': ' . $message);
    }

    /**
     * Logs a message of priority error
     *
     * @param string $message
     */
    public function error($message)
    {
        Xinc_Logger::getInstance()->error('[build] ' . $this->getProject()->getName() . ': ' . $message);
    }

    public function build()
    {
        Xinc_Logger::getInstance()->setBuildLogFile(null);
        Xinc_Logger::getInstance()->emptyLogQueue();
        Xinc::setCurrentBuild($this);

        $buildLogFile = Xinc::getInstance()->getStatusDir() . DIRECTORY_SEPARATOR . $this->getProject()->getName()
            . DIRECTORY_SEPARATOR . 'buildlog.xml';
        if (file_exists($buildLogFile)) {
            self::info('Removing old logfile "' . $buildLogFile . '" with size: ' . filesize($buildLogFile));
            unlink($buildLogFile);
        }
        Xinc_Logger::getInstance()->setBuildLogFile($buildLogFile);

        $this->getEngine()->build($this);
        //Xinc_Logger::getInstance()->flush();
        Xinc_Logger::getInstance()->setBuildLogFile(null);

        if (Xinc_Build_Interface::STOPPED != $this->getStatus()) {
            $this->setStatus(Xinc_Build_Interface::INITIALIZED);
        }
    }

    public function updateTasks()
    {
        $this->_setters = Xinc_Plugin_Repository::getInstance()->getTasksForSlot(Xinc_Plugin_Slot::PROJECT_SET_VALUES);

        $this->getProperties()->set('project.name', $this->getProject()->getName());
        $this->getProperties()->set('build.number', $this->getNumber());
        $this->getProperties()->set('build.label', $this->getLabel());

        $builtinProps = Xinc::getInstance()->getBuiltinProperties();

        foreach ($builtinProps as $prop => $value) {
            $this->getProperties()->set($prop, $value);
        }

        $tasks = $this->getTaskRegistry()->getTasks();

        while ($tasks->hasNext()) {
            $task = $tasks->next();
            $this->updateTask($task);
        }
    }

    public static function generateStatusSubDir($projectName, $buildTime)
    {
        $oldTimeZone = ini_get('date.timezone');
        if (Xinc_Timezone::getIniTimezone() == null) {
            ini_set('date.timezone', 'UTC');
        }
        $yearMonthDay = date('Ymd', $buildTime);
        $subDirectory = $projectName;
        $subDirectory .= DIRECTORY_SEPARATOR;
        $subDirectory .= $yearMonthDay . DIRECTORY_SEPARATOR . $buildTime;
        if (Xinc_Timezone::getIniTimezone() == null) {
            ini_set('date.timezone', $oldTimeZone);
        }
        return $subDirectory;
    }

    public function getStatusSubDir()
    {
        $subDirectory = self::generateStatusSubDir($this->getProject()->getName(), $this->getBuildTime());
        return $subDirectory;
    }

    private function updateTask(Xinc_Plugin_Task_Interface &$task)
    {
        $element = $task->getXml();
        foreach ($element->attributes() as $name => $value) {
            $setter = 'set' . $name;
            /**
             * Call PROJECT_SET_VALUES plugins
             */
            while ($this->_setters->hasNext()) {
                $setterObj = $this->_setters->next();
                $value = $setterObj->set($this, $value);
            }
            $this->_setters->rewind();
            $task->$setter((string) $value, $this);
        }

        $subtasks = $task->getTasks();

        while ($subtasks->hasNext()) {
            $this->updateTask($subtasks->next());
        }
    }

    public function enqueue()
    {
        $this->_isQueued = true;
    }

    /**
     * check if build is in queue mode
     *
     */
    public function isQueued()
    {
        return $this->_isQueued;
    }

    /**
     * remove build from queue mode
     *
     */
    public function dequeue()
    {
        $this->_isQueued = false;
    }

    /**
     * Sets custom config value for the current build
     *
     * @param string $name
     * @param string $value
     */
    public function setConfigDirective($name, $value)
    {
        $this->_config[$name] = $value;
    }
    /**
     * Returns the configuration directive for the name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getConfigDirective($name)
    {
        return isset($this->_config[$name]) ? $this->_config[$name] : null;
    }

    public function resetConfigDirective()
    {
        $this->_config = array();
    }
}
