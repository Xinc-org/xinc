<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Plugin
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

require_once 'Xinc/Plugin/Task/Abstract.php';
require_once 'Xinc/Build/Scheduler/Interface.php';

abstract class Xinc_Trigger_Task_AbstractTask extends Xinc_Plugin_Task_Abstract implements Xinc_Build_Scheduler_Interface
{
    /**
     * @var integer Task Slot INIT_PROCESS
     */
    protected $pluginSlot = Xinc_Plugin_Slot::INIT_PROCESS;

    /**
     * @var string Name of the task
     */
    protected $nextBuildTime = null;

    /**
     * Initialize the task
     *
     * @param Xinc_Build_Interface $build Build to initialize this task for.
     *
     * @return void
     */
    public function init(Xinc_Build_Interface $build)
    {
        $build->addScheduler($this);
    }

    /**
     * Process the task
     *
     * @param Xinc_Build_Interface $build Build to process this task for.
     *
     * @return void
     */
    public function process(Xinc_Build_Interface $build)
    {
        if (time() >= $this->nextBuildTime) {
            $this->nextBuildTime = null;
        }
    }

    /**
     * Gets the last calculated build timestamp.
     *
     * @param Xinc_Build_Interface $build
     *
     * @return integer next build timestamp
     */
    public function getNextBuildTime(Xinc_Build_Interface $build)
    {
        if ($this->nextBuildTime === null) {
            $this->nextBuildTime = $this->getNextTime($build);
        }
        return $this->nextBuildTime;
    }

    /**
     * Calculates the real next build timestamp.
     *
     * @param Xinc_Build_Interface $build
     *
     * @return integer next build timestamp
     */
    abstract function getNextTime(Xinc_Build_Interface $build);
}
