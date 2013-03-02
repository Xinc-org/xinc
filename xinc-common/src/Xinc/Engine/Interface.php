<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Engine to build projects
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Engine
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

interface Xinc_Engine_Interface
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
    public function build(Xinc_Build_Interface &$build);
    
    /**
     * Parses Project-Xml and returns
     *
     * @param Xinc_Project_Config_Iterator $projects
     *
     * @return Xinc_Build_Iterator
     */
    public function parseProjects(Xinc_Project_Iterator $projects);
    
    /**
     * returns the interval in seconds in which
     * the engine checks for new builds
     *
     * @return integer
     */
    public function getHeartBeat();
    
    /**
     * Set the interal in which the engine checks
     * for modified builds, necessary builds etc
     *
     * @param unknown_type $seconds
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