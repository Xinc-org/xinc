<?php
/**
 * Xinc - Continuous Integration.
 * Parser for the Sunrise Engine
 *
 * Parses a project xml for the sunrise engine
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Engine.Sunrise
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

namespace Xinc\Server\Engine\Sunrise;

class Parser
{
    /**
     * All the plugins that parse values
     * before they are set on the task processors
     *
     * @var Xinc\Core\Iterator
     */
    private $setters;

    /**
     * @var Xinc\Server\Engine\Sunrise
     */
    private $engine;

    /**
     *
     * @param Xinc\Server\Engine\Sunrise $engine
     */
    public function __construct(\Xinc\Server\Engine\Sunrise $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Parses the projects xml
     * loads all the tasks
     * assigns them to the builds
     *
     * @param Xinc\Core\Project\Iterator $projects
     *
     * @return array the builds
     */
    public function parseProjectOld($project)
    {
        $builds = array();
//         $this->setters = Xinc_Plugin_Repository::getInstance()
//             ->getTasksForSlot(Xinc_Plugin_Slot::PROJECT_SET_VALUES);

        $build = null;
        /**
         * trying to recover the last build information
         */
        try {
            $build = \Xinc\Core\Build::unserialize($project);
            $build->setBuildTime(null);
            $build->resetConfigDirective();
        } catch (Xinc_Build_Exception_NotFound $e) {
            Xinc_Logger::getInstance()->info(
                'No status data found for ' . $project->getName()
            );
        } catch (Exception $e) {
            Xinc_Logger::getInstance()->error(
                'Could not unserialize old status of ' . $project->getName()
            );
        }
        $projectXml = $project->getConfig();
        if (!$build instanceof Xinc_Build_Interface) {
            $build = new Xinc_Build($this->engine, $project);
        }

        $build->getProperties()->set('project.name', $project->getName());
        $build->getProperties()->set('build.number', $build->getNumber());
        $build->getProperties()->set('build.label', $build->getLabel());

        $builtinProps = Xinc::getInstance()->getBuiltinProperties();

        foreach ($builtinProps as $prop => $value) {
            $build->getProperties()->set($prop, $value);
        }

        $taskRegistry = new Xinc_Build_Tasks_Registry();
        $this->_parseTasks($build, $projectXml, $taskRegistry);

        $build->setTaskRegistry($taskRegistry);
        $build->process(Xinc_Plugin_Slot::PROJECT_INIT);

        if (!$build->haveScheduler()) {
            // set default scheduler
            $scheduler = new Xinc_Build_Scheduler_Default();
            $build->addScheduler($scheduler);
        }

        $labeler = $build->getLabeler();

        if ($labeler == null) {
            // set default scheduler
            $labeler = new Xinc_Build_Labeler_Default();
            $build->setLabeler($labeler);
        }

        $builds[] = $build;

        return $builds;
    }

    public function parseProject($project)
    {
        try {
            $taskRegistry = \Xinc\Core\Task\Registry::getInstance();
            $this->parseTasks(null, $project->getConfig(), $taskRegistry);
            $project->setTaskRegistry($taskRegistry);
        } catch (\Exception $e) {
            \Xinc\Core\Logger::getInstance()->error($e->getMessage());
            $project->setStatus(\Xinc\Core\Project\Status::MISCONFIGURED);
        }
    }

    /**
     * Parses the tasks/subtasks of a project-xml
     *
     * @param SimpleXmlElement $element
     * @param Xinc $project
     */
    private function parseTasks($build, $element, $taskRegistry)
    {
        foreach ($element as $taskName => $task) {
            $taskObject = \Xinc\Core\Task\Registry::getInstance()->get($taskName);
            $taskObject->init(null);
            $taskObject->setXml($task);
            foreach ($task->attributes() as $name => $value) {
                $method = 'set' . ucfirst(strtolower($name));
                if (method_exists($taskObject, $method)) {
                    $taskObject->$method((string)$value, $build);
                } else {
                    \Xinc\Core\Logger::getInstance()->error(
                        'Trying to set "' . $name .'" on task "' . $taskName . '" failed. No such setter.'
                    );
                }
            }

            $this->parseTasks($build, $task, $taskObject);

            if (!$taskObject->validate()) {
                \Xinc\Core\Logger::getInstance()->error('Error validating config.xml for task: ' . $taskObject->getName());
                $project->setStatus(Xinc_Project_Status::MISCONFIGURED);
                return;
            }
        }
    }
}
