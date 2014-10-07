<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Trigger
 * @subpackage Trigger
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2013 Alexander Opitz, Leipzig
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *             This file is part of Xinc.
 *             Xinc is free software; you can redistribute it and/or modify
 *             it under the terms of the GNU Lesser General Public License as
 *             published by the Free Software Foundation; either version 2.1 of
 *             the License, or (at your option) any later version.
 *
 *             Xinc is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Lesser General Public License for more details.
 *
 *             You should have received a copy of the GNU Lesser General Public
 *             License along with Xinc, write to the Free Software Foundation,
 *             Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link       http://code.google.com/p/xinc/
 */

namespace Xinc\Trigger\Task;

abstract class TaskAbstract extends \Xinc\Core\Task\TaskAbstract //implements Xinc_Build_Scheduler_Interface
{
    /**
     * @var integer Task Slot INIT_PROCESS
     */
    protected $pluginSlot = \Xinc\Core\Task\Slot::INIT_PROCESS;

    /**
     * @var string Name of the task
     */
    protected $nextBuildTime = null;

    /**
     * Initialize the task
     *
     * @param Xinc\Core\Job\JobInterface $job Build to initialize this task for.
     *
     * @return void
     */
    public function init(\Xinc\Core\Job\JobInterface $job)
    {
        $job->addScheduler($this);
    }

    /**
     * Process the task
     *
     * @param Xinc\Core\Job\JobInterface $job Build to process this task for.
     *
     * @return void
     */
    public function process(\Xinc\Core\Job\JobInterface $job)
    {
        if (time() >= $this->nextBuildTime) {
            $this->nextBuildTime = null;
        }
    }

    /**
     * Gets the last calculated build timestamp.
     *
     * @param Xinc\Core\Job\JobInterface $job
     *
     * @return integer next build timestamp
     */
    public function getNextBuildTime(\Xinc\Core\Job\JobInterface $job)
    {
        if ($this->nextBuildTime === null) {
            $this->nextBuildTime = $this->getNextTime($job);
        }
        return $this->nextBuildTime;
    }

    /**
     * Calculates the real next build timestamp.
     *
     * @param Xinc\Core\Job\JobInterface $job
     *
     * @return integer next build timestamp
     */
    abstract function getNextTime(\Xinc\Core\Job\JobInterface $job);
}
