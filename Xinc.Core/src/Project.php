<?php
/**
 * Xinc - Continuous Integration.
 * This class represents the project to be continuously integrated
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
 * @author    David Ellis <username@example.com>
 * @author    Gavin Foster <username@example.com>
 * @author    Arno Schneider <username@example.com>
 * @copyright 2007 David Ellis, One Degree Square
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

namespace Xinc\Core;

class Project
{
    /**
     * The xml content of this projects configuration
     *
     * @var Xinc\Core\Project\Config\File
     */
    private $config;

    /**
     *
     * @param Xinc\Core\Project\Config\File $config
     */
    public function setConfig(Project\Config\File $config)
    {
        $this->config = $config;
    }

    /**
     * @return Xinc_Project_Config_File
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Logs a message of priority info
     *
     * @param string $message
     */
    public function info($message)
    {
        Xinc_Logger::getInstance()->info('[project] ' . $this->getName() . ': '.$message);

    }

    /**
     * Logs a message of priority warn
     *
     * @param string $message
     */
    public function warn($message)
    {
        Xinc_Logger::getInstance()->warn('[project] ' . $this->getName() . ': '.$message);

    }

    /**
     * Logs a message of priority verbose
     *
     * @param string $message
     */
    public function verbose($message)
    {
        Xinc_Logger::getInstance()->verbose('[project] ' . $this->getName() . ': '.$message);

    }

    /**
     * Logs a message of priority debug
     *
     * @param string $message
     */
    public function debug($message)
    {
        Xinc_Logger::getInstance()->debug('[project] ' . $this->getName() . ': '.$message);

    }

    /**
     * Logs a message of priority error
     *
     * @param string $message
     */
    public function error($message)
    {
        Xinc_Logger::getInstance()->error('[project] ' . $this->getName() . ': '.$message);

    }
}
