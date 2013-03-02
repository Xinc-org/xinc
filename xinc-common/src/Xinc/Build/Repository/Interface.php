<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Repository to get historic build information
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build.Repository
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

interface Xinc_Build_Repository_Interface
{
    /**
     * Gets a build defined by its project name and buildTime
     *
     * @param string $projectName
     * @param integer $buildTime
     *
     * @return Xinc_Build_Interface
     * @throws Xinc_Build_Exception_Unserialization
     * @throws Xinc_Build_Exception_NotFound
     */
    public static function getBuild(Xinc_Project $project, $buildTime);
    
    /**
     * Gets the last build for a project.
     * 
     * Loads it from its serialized form
     *
     * @param Xinc_Project $project
     *
     * @return Xinc_Build_Interface
     * @throws Xinc_Build_Exception_Unserialization
     * @throws Xinc_Build_Exception_NotFound
     */
    public static function getLastBuild(Xinc_Project $project);
}