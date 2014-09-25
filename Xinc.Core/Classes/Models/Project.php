<?php
/**
 * Xinc - Continuous Integration.
 * This model represents one project with its processes.
 * It is loaded from the configuration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
 * @author    Alexander Opitz <opitz.alexander@googlemail.com>
 * @copyright 2014 Alexander Opitz, Leipzig
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

namespace Xinc\Core\Models;

class Project
{
    /**
     * @var string The name of the project.
     */
    private $name = '';

    /**
     * @var string Name of the used engine.
     */
    private $engineName = '';

    /**
     * @see Xinc\Core\Project\Status
     * @var integer Current status of the project
     */
    private $status = \Xinc\Core\Project\Status::NEVERRUN;

    /**
     * @see Xinc\Core\Plugin\Slot
     * @var array Used Processes
     */
    private $processes = array();

    /**
     * @var Xinc\Core\Models\ProjectGroup The group this project belongs
     */
    private $group = null;

    // TODO: Not the right direction.
    private $config;

    /**
     * Sets the project name for display purposes.
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns this name of the project.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the project name of the used engine.
     *
     * @param string $engine
     * @return void
     */
    public function setEngineName($engineName)
    {
        $this->engineName = $engineName;
    }

    /**
     * Returns this name of the engine of this project.
     *
     * @return string
     */
    public function getEngineName()
    {
        return $this->engineName;
    }

    /**
     * sets the status of the project
     *
     * @see Xinc\Core\Project\Status
     * @param integer $status
     * @return void
     */
    public function setStatus($status)
    {
        // @TODO Nothing a model should do.
        \Xinc\Core\Logger::getInstance()->info('[project] ' . $this->getName() . ': Setting status to ' . $status);
        $this->status = $status;
    }

    /**
     * Retrieves the status of the current project
     * @see Xinc\Core\Project\Status
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setGroup(ProjectGroup $group)
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Adds a process with appropriate slot to the project
     *
     * @param integer $slot
     * @param ? $process
     * @return void
     */
    public function addProcess($slot, $process)
    {
        $this->processes[$slot][] = $process;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
