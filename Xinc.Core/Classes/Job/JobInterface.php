<?php
/**
 * Xinc - Continuous Integration.
 * Job interface
 *
 * Used by the engines to process a build
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
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

namespace Xinc\Core\Job;

interface JobInterface
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
    public function __construct(
        Xinc\Server\Engine\EngineInterface $engine, // @TODO Decouple
        \Xinc\Core\Models\Project $project,
        $buildTimestamp = null
    );

    /**
     * Returns the last build
     *
     * @return Xinc_Build_Interface
     */
    public function getLastBuild();

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
    public function getProperties();

    /**
     * returns the internal build properties
     *
     * @return Xinc_Build_Properties
     */
    public function getInternalProperties();

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
     * Returns the next build time (unix timestamp)
     * for this build
     *
     */
    public function getNextBuildTime();

    /**
     * stores the build information
     *
     */
    public function serialize();

    /**
     * loads the build information
     *
     */
    public static function unserialize(Xinc_Project &$project, $buildTimestamp = null, $statusDir = null);

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
