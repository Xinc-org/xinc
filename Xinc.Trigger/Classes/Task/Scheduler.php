<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Trigger
 * @author     Arno Schneider <username@example.org>
 * @copyright  2007 Arno Schneider, Barcelona
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

class Scheduler extends TaskAbstract
{
    /**
     * @var string Name of the task
     */
    protected $name = 'schedule';

    /**
     * @var integer Number of seconds to wait, till next build initialisation.
     */
    private $interval;

    /**
     * Sets the interval in seconds.
     *
     * @param string The interval in seconds as numerical representation.
     *
     * @return void
     */
    public function setInterval($interval)
    {
        $this->interval = (int) $interval;
    }

    /**
     * Gets the interval in seconds.
     *
     * @return integer Interval in seconds.
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate()
    {
        return $this->interval > 0;
    }

    /**
     * Calculates the next build timestamp.
     *
     * @param Xinc\Core\Job\JobInterface $job
     *
     * @return integer next build timestamp
     */
    public function getNextTime(\Xinc\Core\Job\JobInterface $job)
    {
        if ($build->getStatus() == \Xinc\Core\Job\JobInterface::STOPPED) {
            return null;
        }
        $lastBuild = $build->getLastBuild()->getBuildTime();

        if ($lastBuild != null ) {
            $nextBuild = $this->getInterval() + $lastBuild;
            /**
             * Make sure that we dont rerun every build if the daemon was paused
             */
            if ($nextBuild + $this->getInterval() < time()) {
                $nextBuild = time();
            }
        } else {
            // never ran, schedule for now
            $nextBuild = time();
        }

        $build->debug(
            'getNextBuildTime:'
            . ' lastbuild: ' . date('Y-m-d H:i:s', $lastBuild)
            . ' nextbuild: ' . date('Y-m-d H:i:s', $nextBuild)
        );

        return $nextBuild;
    }
}
