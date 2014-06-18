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
    public function parseProjects(\Xinc\Core\Project\Iterator $projects)
    {
        $builds = array();
//         $this->setters = Xinc_Plugin_Repository::getInstance()
//             ->getTasksForSlot(Xinc_Plugin_Slot::PROJECT_SET_VALUES);

        foreach($projects as $key => $project) {
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
        }
        return $builds;
    }

    /**
     * Parses the task of a project-xml
     *
     * @param SimpleXmlElement $element
     * @param Xinc $project
     */
    private function _parseTasks(Xinc_Build_Interface &$build, &$element, &$repository)
    {
        $project = $build->getProject();

        foreach ($element->children() as $taskName => $task) {
            try{
                $taskObject = Xinc_Plugin_Repository::getInstance()->getTask($taskName, (string)$element);
                $taskObject->init($build);
                $taskObject->setXml($task);
            } catch(Exception $e){
                Xinc_Logger::getInstance()->error('undefined task "'
                                                 .$taskName.'"');
                //throw new Xinc_Exception_MalformedConfig();
                $project->setStatus(Xinc_Project_Status::MISCONFIGURED);
                return;
            }
            foreach ($task->attributes() as $name => $value) {
                $setter = 'set'.$name;
                /**
                 * Call PROJECT_SET_VALUES plugins
                 */
                while ($this->setters->hasNext()) {
                    $setterObj = $this->setters->next();
                    $value = $setterObj->set($build, $value);
                }
                $this->setters->rewind();
                $taskObject->$setter((string)$value, $build);
            }

            $this->_parseTasks($build, $task, $taskObject);
            $repository->registerTask($taskObject);


            if ( !$taskObject->validate() ) {
                //throw new Xinc_Exception_MalformedConfig('Error validating '
                //                                        .'config.xml for task: '
                //                                        .$taskObject->getName());
                $project->setStatus(Xinc_Project_Status::MISCONFIGURED);
                return;
            }
        }
    }
}
