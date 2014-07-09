<?php
/**
 * Parses an array of SimpleXMLElements and generates Projects out of it
 *
 * @package   Xinc.Core
 * @author    Arno Schneider <username@example.com>
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

namespace Xinc\Core\Project\Config;

class Parser
{
    /**
     * @var Xinc\Core\Project\Config\File
     */
    private $configFile;

    public function __construct(File $configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * generates an array of all configured projects
     *
     * @return Xinc\Core\Project\Iterator
     */
    public function getProjects()
    {
        $projects = $this->configFile->xpath("//project");
        return new Iterator($projects);
    }

    /**
     * Returns the name of the engine that has to be used for these Projects
     * @return string|null String or null if not found
     */
    public function getEngineName()
    {
        $xincAttributes = $this->configFile->attributes();
        if (isset($xincAttributes['engine'])) {
            return (string) $xincAttributes['engine'];
        }
        return null;
    }

    /**
     * Returns the name of the group for this projects.
     * @return string|null String or null if not found
     */
    public function getName()
    {
        $xincAttributes = $this->configFile->attributes();
        if (isset($xincAttributes['engine'])) {
            return (string) $xincAttributes['engine'];
        }
        return null;
    }
}
