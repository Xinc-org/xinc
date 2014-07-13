<?php
/**
 * Xinc - Continuous Integration.
 * First Xinc Engine running on XML
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
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Server\Engine;

class Sunrise implements EngineInterface
{
    const NAME = 'Sunrise';

    /**
     * @var integer The min wake up of the engine in seconds.
     */
    private $heartBeat;

    /**
     * @var Xinc_Build_Interface The current build
     */
    private $build;

    private $projects = array();

    /**
     * Setup worker
     *
     * @return void
     */
    public function setup()
    {
        $this->mediator->log('Setup');
    }

    /**
     * serializes the build before shutting down
     *
     * @return void
     */
    public function tearDown()
    {
        if (!\Core_Daemon::is('parent')) {
            $this->mediator->log('TearDown');
            if ($this->build != null) {
                $this->serializeBuild($this->build);
                $this->build = null;
            }
        }
    }

    public function check_environment(array $errors = array())
    {

    }

    /**
     * Start the engines own loop
     *
     * @return void
     */
    public function doWork()
    {
        $this->mediator->log('Sunrise is working');
        while (!\Core_Daemon::is('shutdown')) {
            usleep(1000);
        }
    }

    /**
     * get the name of this engine
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Adds a project to the engine.
     *
     * @param \Xinc\Core\Models\Project $project A project inside this engine.
     *
     * @return void
     */
    public function addProject(\Xinc\Core\Models\Project $project)
    {
        $this->mediator->log('Add project ' . $project->getName());
        $this->projects[] = $project;
        $this->parseProject($project);
    }

    private function handleBuildConfig(Xinc_Build_Interface $build)
    {
        $logLevel = $build->getConfigDirective('loglevel');
        if ($logLevel !== null) {
            Xinc_Logger::getInstance()->setLogLevel($logLevel);
        }
        $timezone = $build->getConfigDirective('timezone');
        if ($timezone !== null) {
            Xinc_Timezone::set($timezone);
        }
    }

    /**
     * Clean up after a build is completed.
     *
     * @return void
     */
    private function endBuild()
    {
        $this->build = null;
        Xinc::getInstance()->restoreConfigDirectives();
    }

    /**
     * Serializes a build an catches the exceptions
     *
     * @param Xinc_Build_Interface $build
     *
     * @return void
     */
    protected function serializeBuild(Xinc_Build_Interface $build)
    {
        try {
            $build->serialize();
        } catch (Xinc_Build_Exception_NotRun $e1) {
            $build->error('Build cannot be serialized, it did not run.');
        } catch (Xinc_Build_Exception_Serialization $e2) {
            $build->error('Build could not be serialized properly.');
        } catch (Xinc_Build_History_Exception_Storage $e3) {
            $build->error('Build history could not be stored.');
        } catch (Exception $e4) {
            $build->error('Unknown error occured while serializing the build.');
        }
    }

    /**
     * Process a build
     *
     * @param Xinc_Build_Interface $build
     */
    public function build(Xinc_Build_Interface $build)
    {
        /**
         * handles the configuration of this build and sets all the options
         */
        $this->handleBuildConfig($build);
        $this->build = $build;
        $buildTime = time();
        $startTime = microtime(true);
        $build->setBuildTime($buildTime);
        $build->init();

        if (Xinc_Build_Interface::STOPPED === $build->getStatus()) {
            return $this->endBuild();
        }

        /**
         * Increasing the build number, if it fails we need to decrease again
         */
        if (Xinc_Build_Interface::PASSED === $build->getLastBuild()->getStatus()
            || (null === $build->getLastBuild()->getStatus() &&
                Xinc_Build_Interface::STOPPED !== $build->getLastBuild()->getStatus())
        ) {
            $build->setNumber($build->getNumber() + 1);
            //$this->updateBuildTasks($build);
        }

        $build->updateTasks();
        $build->process(Xinc_Plugin_Slot::INIT_PROCESS);

        if (Xinc_Build_Interface::STOPPED === $build->getStatus()) {
            Xinc_Logger::getInstance()->info('Build of Project stopped in INIT phase');
            $build->setLastBuild();
            return $this->endBuild();
        }

        Xinc_Logger::getInstance()->info('CHECKING PROJECT ' . $build->getProject()->getName());
        $build->process(Xinc_Plugin_Slot::PRE_PROCESS);

        if (Xinc_Build_Interface::STOPPED === $build->getStatus()) {
            $build->info('Build of Project stopped, no build necessary');
            $build->setStatus(Xinc_Build_Interface::INITIALIZED);
            $build->setLastBuild();
            return $this->endBuild();
        } elseif (Xinc_Build_Interface::FAILED === $build->getStatus()) {
            //$build->setBuildTime($buildTime);
            $build->updateTasks();
            $build->error("Build failed");
            /**
             * Process failed in the pre-process phase, we need
             * to run post-process to maybe inform about the failed build
             */
            $build->process(Xinc_Plugin_Slot::POST_PROCESS);
            /**
             * Issue 79, we need to serialize the build after failure in preprocess
             */
            /**
             * set the "time it took to build" on the build
             */
            $endTime = microtime(true);
            $build->getStatistics()->set('build.duration', $endTime - $startTime);

            $this->serializeBuild($build);
            return $this->endBuild();
        } elseif (Xinc_Build_Interface::PASSED === $build->getStatus()) {

            $build->info('Code not up to date, building project');
            $build->updateTasks();
            $build->process(Xinc_Plugin_Slot::PROCESS);
            if (Xinc_Build_Interface::PASSED == $build->getStatus()) {
                $build->updateTasks();
                $build->info("BUILD PASSED");
            } elseif (Xinc_Build_Interface::STOPPED == $build->getStatus()) {
                //$build->setNumber($build->getNumber()-1);
                $build->updateTasks();
                $build->warn("BUILD STOPPED");
            } elseif (Xinc_Build_Interface::FAILED == $build->getStatus()) {
                //if ($build->getLastBuild()->getStatus() == Xinc_Build_Interface::PASSED) {
                //    $build->setNumber($build->getNumber()+1);
                //}
                $build->updateTasks();
                $build->error("BUILD FAILED");
            }

            $processingPast = $build->getStatus();
            /**
             * Post-Process is run on Successful and Failed Builds
             */
            $build->process(Xinc_Plugin_Slot::POST_PROCESS);

            /**
             * set the "time it took to build" on the build
             */
            $endTime = microtime(true);
            $build->getStatistics()->set('build.duration', $endTime - $startTime);

            $this->serializeBuild($build);

            return $this->endBuild();
        } elseif (Xinc_Build_Interface::INITIALIZED === $build->getStatus()) {
            //$build->setBuildTime($buildTime);
            if ($build->getLastBuild()->getStatus() === null) {
                $build->setNumber($build->getNumber()-1);
            }
            $build->setStatus(Xinc_Build_Interface::STOPPED);
            $this->serializeBuild($build);
            return $this->endBuild();
        } else {
            $build->setStatus(Xinc_Build_Interface::STOPPED);
            $build->setLastBuild();
            return $this->endBuild();
        }
    }

    /**
     * Parses Project-Xml and returns
     *
     * @throws Xinc\Core\Build\Exception\InvalidException
     */
    public function parseProject($project)
    {
        $parser = new Sunrise\Parser($this);
        $buildsArr = $parser->parseProject($project);

        $this->buildIterator = new \Xinc\Core\Build\Iterator($buildsArr);
    }

    /**
     * returns the interval in seconds in which the engine checks for new builds
     *
     * @return integer
     */
    public function getHeartBeat()
    {
        return $this->heartBeat;
    }

    /**
     * Set the interal in which the engine checks for modified builds, necessary builds etc
     *
     * see <xinc engine="name" heartbeat="10"/>
     *
     * @param string $seconds
     */
    public function setHeartBeat($seconds)
    {
        $this->heartBeat = (int) $seconds;
    }
}
