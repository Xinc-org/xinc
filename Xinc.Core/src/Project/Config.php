<?php
/**
 * Main configuration class, handles the system.xml
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

namespace Xinc\Core\Project;

class Config
{
    
    /**
     *  Iterator holding all the project configuration in xml
     *
     *  @var Xinc\Core\Project\Config\Iterator
     */
    private $projectConfigs;
    
    /**
     * Iterator holding all the configured projects
     *
     * @var Xinc\Core\Project\Iterator
     */
    private $projects;
    
    /**
     * Name of the engine
     *
     * @var string
     */
    private $engineName;
    /**
     * Reads the system.xml
     * - parses it
     * - loads projects
     *
     * @param string $fileName path to system.xml
     *
     * @throws Xinc\Core\Project\Config\Exception\FileNotFound
     * @throws Xinc\Core\Project\Config\Exception\InvalidEntry
     */
    public function __construct($fileName)
    {
        $configFile = Config\File::load($fileName);
        $configParser = new Config\Parser($configFile);
        
        $this->projectConfigs = $configParser->getProjects();
        $this->engineName = $configParser->getEngineName();
        $this->generateProjects();
    }
    
    private function generateProjects()
    {
        $projects = array();

        while ($this->projectConfigs->hasNext()) {
            $projectConfig = $this->projectConfigs->next();
            
            $projectObject = new \Xinc\Core\Project();
            
            foreach ($projectConfig->attributes() as $name => $value ) {
                $method = 'set' . ucfirst(strtolower($name));
                /**
                 * Catch unsupported methods by checking if method exists or 
                 * having a magic function __set and __get on all objects
                 */
                if (method_exists($projectObject, $method)) {
                    $projectObject->$method((string)$value);
                } else {
                    \Xinc\Core\Logger::getInstance()->error(
                        'Trying to set "' . $name .'" on Xinc Project failed. No such setter.'
                    );
                }
            }
            $projectObject->setConfig($projectConfig);
            $projects[] = $projectObject;
        }
        
        $this->projects = new Iterator($projects);
    }
    
    
    /**
     * returns the configured Projects
     *
     * @return Xinc\Core\Project\Iterator
     */
    public function getProjects()
    {
        return $this->projects;
    }
    
    public function getEngineName()
    {
        return $this->engineName;
    }
}
