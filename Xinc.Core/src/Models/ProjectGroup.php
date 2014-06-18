<?php
/**
 * Xinc - Continuous Integration.
 * This model represents one group of projects.
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

class ProjectGroup
{
    /**
     * @var string The name of the project.
     */
    private $name;

    /**
     * @var array Used Processes
     */
    private $projects = array();

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
     * Returns this project's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds a project to this group.
     *
     * @param Xinc\Core\Models\Project $project
     * @return void
     */
    public function addProject(Project $project)
    {
        $this->projects[] = $project;
    }

    /**
     * Returns the projects in this group.
     *
     * @return array
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
