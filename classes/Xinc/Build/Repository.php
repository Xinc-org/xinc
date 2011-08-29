<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Build Repository provides helper methods to load builds of a project
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build
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

require_once 'Xinc/Build/Repository/Interface.php';
require_once 'Xinc/Build/History.php';

class Xinc_Build_Repository implements Xinc_Build_Repository_Interface
{
    /**
     * Gets a build defined by its project name and buildTime
     *
     * @param string $projectName
     * @param integer $buildTime
     * @throws Xinc_Build_Exception_Unserialization
     * @throws Xinc_Build_Exception_NotFound
     * @return Xinc_Build_Interface
     */
    public static function getBuild(Xinc_Project $project, $buildTime)
    {
        $statusDir = null;
        if (class_exists('Xinc_Gui_Handler')) {
            $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        } else if (class_exists('Xinc')) {
            $statusDir = Xinc::getInstance()->getStatusDir();
        }
        return Xinc_Build::unserialize($project, $buildTime, $statusDir);
    }
    
    /**
     * Gets the last build for a project.
     * 
     * Loads it from its serialized form
     *
     * @param Xinc_Project $project
     * @throws Xinc_Build_Exception_Unserialization
     * @throws Xinc_Build_Exception_NotFound
     * @return Xinc_Build_Interface
     */
    public static function getLastBuild(Xinc_Project $project)
    {
        $lastBuildTime = Xinc_Build_History::getLastBuildTime($project);
        //$lastBuildTime = $buildHistoryArr[count($buildHistoryArr) - 1];
        return self::getBuild($project, $lastBuildTime);
    }
    
    public static function getLastSuccessfulBuild(Xinc_Project &$project)
    {
        $lastBuildTime = Xinc_Build_History::getLastSuccessfulBuildTime($project);
        //$lastBuildTime = $buildHistoryArr[count($buildHistoryArr) - 1];
        return self::getBuild($project, $lastBuildTime);
    }
}