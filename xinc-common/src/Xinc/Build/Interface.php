<?php
/**
 * Xinc - Continuous Integration.
 * Build interface
 *
 * Used by the engines to process a build
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
 * @link      http://xincplus.sourceforge.net
 */

interface Xinc_Build_Interface
{
    const INITIALIZED = -2;
    const FAILED = 0;
    const PASSED = 1;
    const STOPPED = -1;
    const MISCONFIGURED = 3;

     /** 
     * sets the project, engine
     * and timestamp for the build
     *
     * @param Xinc_Engine_Interface $engine
     * @param Xinc_Project $project
     * @param integer $buildTimestamp
     */
    public function __construct(Xinc_Engine_Interface &$engine,
                                Xinc_Project &$project,
                                $buildTimestamp = null);
    
    /**
     * Returns the last build
     * @return Xinc_Build_Interface
     */
    public function &getLastBuild();
    
    /**
     * Moves the current build to _lastBuild
     *
     */
    public function setLastBuild();
    
    /**
     * returns the build properties
     *
     * @return Xinc_Build_Properties
     */
    public function &getProperties();
    
    /**
     * returns the internal build properties
     *
     * @return Xinc_Build_Properties
     */
    public function &getInternalProperties();
    
    /**
     * called before a new build is executed
     *
     */
    public function init();
    /**
     * returns the build statistics
     *
     * @return Xinc_Build_Statistics
     */
    public function &getStatistics();
    /**
     * sets the build time for this build
     *
     * @param integer $buildTime unixtimestamp
     */
    public function setBuildTime($buildTime);
    
    /**
     * returns the timestamp of this build
     * @return integer Timestamp of build (unixtimestamp)
     */
    public function getBuildTime();
    

    /**
     * Returns the next build time (unix timestamp)
     * for this build
     *
     */
    public function getNextBuildTime();
    /**
     * 
     * @return Xinc_Project
     */
    public function &getProject();
    
    /**
     * 
     * @return Xinc_Engine_Interface
     */
    public function &getEngine();
    
    /**
     * stores the build information
     *
     */
    public function serialize();
    
    /**
     * loads the build information
     *
     */
    public static function &unserialize(Xinc_Project &$project, $buildTimestamp = null, $statusDir = null);

    /**
     * returns the status of this build
     *
     */
    public function getStatus();
    
    /**
     * Set the status of this build
     *
     * @param integer $status
     */
    public function setStatus($status);
    
    /**
     * Sets the sequence number for this build
     *
     * @param integer $no
     */
    public function setNumber($no);
    
    /**
     * returns the build no for this build
     *
     * @return integer
     */
    public function getNumber();
    
    
    /**
     * Sets the labeler that should be used for this build
     *
     * @param Xinc_Build_Labeler_Interface $labeler
     */
    public function setLabeler(Xinc_Build_Labeler_Interface &$labeler);
    
    /**
     * 
     * @return Xinc_Build_Labeler_Interface
     */
    public function getLabeler();
    /**
     * Sets a build scheduler,
     * which calculates the next build time based
     * on the configuration
     *
     * @param Xinc_Build_Scheduler_Interface $scheduler
     */
    public function setScheduler(Xinc_Build_Scheduler_Interface &$scheduler);
    
    /**
     * @return Xinc_Build_Scheduler_Interface
     */
    public function getScheduler();
    /**
     * returns the label of this build
     *
     * @return string
     */
    public function getLabel();
    
    /**
     *
     * @param Xinc_Build_Tasks_Registry $taskRegistry
     */
    public function setTaskRegistry(Xinc_Build_Tasks_Registry $taskRegistry);
    
    /**
     * @return Xinc_Build_Tasks_Registry
     *
     */
    public function getTaskRegistry();
    
    /**
     * processes the tasks that are registered for the slot
     *
     * @param mixed $slot
     */
    public function process($slot);

    /**
     * Build
     *
     */
    public function build();
    
    /**
     * Updates properties on tasks, after 
     * a change in build status
     *
     */
    public function updateTasks();
    
    /**
     * Returns the subdirectory inside the status directory
     * where the status information of the build is stored
     *
     */
    public function getStatusSubDir();
    
    /**
     * Logs a message of severity info
     *
     * @param string $message
     */
    public function info($message);
    
    public function error($message);
    public function warn($message);
    public function debug($message);
    
    /**
     * Put build into queue mode
     *
     */
    public function enqueue();
    
    /**
     * check if build is in queue mode
     *
     */
    public function isQueued();
    
    /**
     * remove build from queue mode
     *
     */
    public function dequeue();
    
    /**
     * Sets custom config value for the current build
     *
     * @param string $name
     * @param string $value
     */
    public function setConfigDirective($name, $value);
    
    public function resetConfigDirective();
    /**
     *
     * @param string $name
     */
    public function getConfigDirective($name);
}