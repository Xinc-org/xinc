<?php
/**
 * Xinc - Continuous Integration.
 * Engine to build projects
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Server
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

namespace Xinc\Server\Engine;

interface EngineInterface
{

    /**
     * get the name of the engine
     *
     * @return string Name of the engine.
     */
    public function getName();

    /**
     * process the build
     *
     * @param Xinc_Build_Interface $build
     */
    public function build(Xinc_Build_Interface $build);

    /**
     * adds a project to the engine.
     */
    public function addProject(\Xinc\Core\Models\Project $project);

    /**
     * Parses Project-Xml and returns
     *
     * @param \Xinc\Core\Project\Iterator $projects
     *
     * @return Xinc_Build_Iterator
     */
    public function parseProjects(\Xinc\Core\Project\Iterator $projects);

    /**
     * returns the interval in seconds in which the engine checks for new builds
     *
     * @return integer
     */
    public function getHeartBeat();

    /**
     * Set the interval in which the engine checks for modified builds, necessary builds etc
     *
     * @param string $seconds
     *
     * @see <xinc engine="name" heartbeat="10"/>
     */
    public function setHeartBeat($seconds);

    /**
     * Validate if the engine can run properly on this system
     *
     * @return boolean True if engine can run properly otherwise false.
     */
    public function validate();
}
