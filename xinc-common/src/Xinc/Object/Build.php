<?php
/**
 * Xinc - Continuous Integration.
 * This class represents the build that is going to be run with Xinc
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Object
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

namespace Xinc\Object;

class Build
{
    /**
     * @var boolean Are we queued?
     */
    private $isQueued = false;

    /**
     * @var Xinc\Engine\Interface
     */
    private $engine;

    /**
     * @var Xinc\Project
     */
    private $project;

    /**
     * @var Xinc\Build\Properties
     */
    private $properties;

    /**
     * @var Xinc\Build\Properties
     */
    private $internalProperties;

    /**
     * @var Xinc\Build\Statistics
     */
    private $statistics;

    /**
     * @var integer
     */
    private $buildTimestamp;

    /**
     * @var integer
     */
    private $nextBuildTimestamp;

    /**
     * @var integer Build status, as defined in Xinc\Build\Interface
     */
    private $status;

    /**
     * @var Xinc\Build\Interface
     */
    private $lastBuild;

    /**
     * @var integer The build no of this build
     */
    private $number;

    /**
     * @var string The label for this build
     */
    private $label;

    /**
     * @var Xinc\Build\Tasks\Registry Contains tasks that need to be executed for each Process Step
     */
    private $taskRegistry;

    /**
     * @var \Xinc\Interfaces\Scheduler[] Build schedulers
     */
    private $schedulers;

    /**
     * @var \Xinc\Interfaces\Labeler
     */
    private $labeler;

    /**
     * @var [] Holding config values for this build
     */
    private $config = array();

    /**
     *
     */
    public function setLabeler(\Xinc\Interfaces\Labeler $labeler)
    {
        $this->labeler = $labeler;
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
    public function getInternalProperties()
    {
        return $this->_internalProperties;
    }

    /**
     * @return Xinc_Build_Statistics
     */
    public function getStatistics()
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
     *
     * @return Xinc_Project
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     *
     * @return Xinc_Engine_Interface
     */
    public function getEngine()
    {
        return $this->_engine;
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
