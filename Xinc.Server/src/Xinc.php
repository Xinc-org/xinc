<?php
/**
 * Xinc - Continuous Integration.
 * The main control class.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Server
 * @author    David Ellis  <username@example.org>
 * @author    Gavin Foster <username@example.org>
 * @author    Jamie Talbot <username@example.org>
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
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

namespace Xinc\Server;

class Xinc
{
    const VERSION = '2.3.90';

    const DEFAULT_PROJECT_DIR = 'projects';
    const DEFAULT_STATUS_DIR = 'status';

    /**
     * Current working directory
     * containing the default xinc projects
     *
     * @var string
     */
    private $workingDir;

    /**
     * Directory holding the projects
     *
     * @var string
     */
    private $projectDir;

    /**
     * The directory to drop xml status files
     *
     * @var string
     */
    private $statusDir;

    /**
     * @var array Holding the merged cli parameters.
     */
    private $options = array();

    /**
     * Handle command line arguments.
     *
     * @return void
     */
    protected function parseCliOptions()
    {
        $workingDir = dirname($_SERVER['argv'][0]);

        $opts = getopt(
            'r:w:s:f:l:v:o',
            array(
                'project-dir:',
                'working-dir:',
                'status-dir:',
                'project-file:',
                'log-file:',
                'pid-file:', // not easy  in Core_Daemon
                'verbose:', // not easy  in Core_Daemon
                'once',  // done
                'version',  // done
                'help',  // done
                'deamon',  // not easy  in Core_Daemon
                '::',
            )
        );

        if (isset($opts['version'])) {
            $this->logVersion();
            exit();
        }

        if (isset($opts['help'])) {
            $this->showHelp();
            exit();
        }

        $this->options = $this->mergeOpts(
            $opts,
            array(
                'w' => 'working-dir',
                'r' => 'project-dir',
                's' => 'status-dir',
                'f' => 'project-file',
                'l' => 'log-file',
                'v' => 'verbose',
                'o' => 'once',
            ),
            array (
                'working-dir' => $workingDir,
                'project-dir' => $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_PROJECT_DIR . DIRECTORY_SEPARATOR,
                'status-dir'  => $workingDir . DIRECTORY_SEPARATOR . self::DEFAULT_STATUS_DIR . DIRECTORY_SEPARATOR,
                'log-file'    => $workingDir . DIRECTORY_SEPARATOR . 'xinc.log',
                'verbose'     => \Xinc\Core\Logger::DEFAULT_LOG_LEVEL,
            )
        );
    }

    /**
     * Validates the given options (working-dir, status-dir, project-dir)
     *
     * @throws Xinc\Core\Exception\IOException
     */
    protected function validateCliOptions()
    {
        $this->checkDirectory($this->options['working-dir']);
        $this->checkDirectory($this->options['project-dir']);
        $this->checkDirectory($this->options['status-dir']);
    }

    /**
     * Merges the default config and the short/long arguments given by mapping together.
     * TODO: It doesn't respect options which aren't in the mapping.
     *
     * @param array $opts The options after php getopt function call.
     * @param array $mapping Mapping from short to long argument names.
     * @param array $default The default values for some arguments.
     *
     * @return array Mapping of the long arguments to the given values.
     */
    protected function mergeOpts($opts, $mapping, $default)
    {
        $merge = $default;

        foreach ($mapping as $keyShort => $keyLong) {
            if (isset($opts[$keyShort])) {
                $merge[$keyLong] = $opts[$keyShort];
            }
            if (isset($opts[$keyLong])) {
                $merge[$keyLong] = $opts[$keyLong];
            }
        }

        return $merge;
    }

    /**
     * Checks if the directory is available otherwise tries to create it.
     * Returns the realpath of the directory afterwards.
     *
     * @param string $strDirectory Directory to check for.
     *
     * @return string The realpath of given directory.
     * @throws Xinc\Core\Exception\IOException
     */
    protected function checkDirectory($strDirectory)
    {
        if (!is_dir($strDirectory)) {
            \Xinc\Core\Logger::getInstance()->verbose(
                'Directory "' . $strDirectory . '" does not exist. Trying to create'
            );
            $bCreated = @mkdir($strDirectory, 0755, true);
            if (!$bCreated) {
                $arError = error_get_last();
                \Xinc\Core\Logger::getInstance()->verbose(
                    'Directory "' . $strDirectory . '" could not be created.'
                );
                throw new \Xinc\Core\Exception\IOException($strDirectory, null, $arError['message']);
            }
        } elseif (!is_writeable($strDirectory)) {
            \Xinc\Core\Logger::getInstance()->verbose(
                'Directory "' . $strDirectory . '" is not writeable.'
            );
            throw new \Xinc\Core\Exception\IOException(
                $strDirectory,
                null,
                null,
                \Xinc\Core\Exception\IOException::FAILURE_NOT_WRITEABLE
            );
        }

        return realpath($strDirectory);
    }

    /**
     * TODO: Needs to be somewhere else?
     * returns the builtin properties that can be used
     * in all xinc config files
     *
     * @return array
     */
//     public function getBuiltinProperties()
//     {
//         $properties = array();
//         $properties['workingdir'] = $this->getWorkingDir();
//         $properties['statusdir'] = $this->getStatusDir();
//         $properties['projectdir'] = $this->getProjectDir();
//
//         return $properties;
//     }

    /**
     * prints help message, describing different parameters to run xinc
     *
     * @return void
     */
    protected function showHelp()
    {
        echo 'Usage: xinc [switches]' . "\n\n";

        echo '  -f --project-file=<file>  The project file to use.' . "\n"
            . '  -l --log-file=<file>      The log file to use.' . "\n"
            . '  -p --pid-file=<file>      The directory to put the PID file' . "\n"
            . '  -r --project-dir=<dir>    The project directory.' . "\n"
            . '  -s --status-dir=<dir>     The status directory to use.' . "\n"
            . '  -w --working-dir=<dir>    The working directory.' . "\n"
            . '  -v --verbose=<level>      The level of information to log (default 2).' . "\n"
            . '  -o --once                 Run once and exit.' . "\n"
            . '  -d --daemon               Daemon, detach and run in the background' . "\n"
            . '  --version                 Prints the version of Xinc.' . "\n"
            . '  -h --help                 Prints this help message.' . "\n";
    }

    /**
     * Prints the startup information of xinc
     *
     * @return void
     */
    public function logStartupSettings()
    {
        $logger = \Xinc\Core\Logger::getInstance();

        $logger->info('Starting up Xinc');
        $logger->info('- Version:    ' . Xinc::VERSION);
        $logger->info('- Workingdir: ' . $this->options['working-dir']);
        $logger->info('- Projectdir: ' . $this->options['project-dir']);
        $logger->info('- Statusdir:  ' . $this->options['status-dir']);
        $logger->info('- Log Level:  ' . $this->options['verbose']);
    }

    /**
     * Prints the version of xinc
     */
    public function logVersion()
    {
        \Xinc\Core\Logger::getInstance()->info('Xinc version ' . Xinc::VERSION);
    }

    /**
     * Initialize the logger with path to file and verbosity
     *
     * @return void
     */
    public function initLogger()
    {
        $logger = \Xinc\Core\Logger::getInstance();

        $logger->setLogLevel($this->options['verbose']);
        $logger->setXincLogFile($this->options['log-file']);
    }

    /**
     * Initialize the Plugins
     * TODO: Not yet done only Sunrise is registered as engine by hand.
     *
     * @return void
     * @TODO Needs work.
     */
    protected function initPlugins()
    {
        // TODO: Add Sunrise Engine now. No Plugable way yet.
        $engine = new \Xinc\Server\Engine\Sunrise();
        Engine\Repository::getInstance()->registerEngine($engine, true);

        \Xinc\Core\Plugin\Repository::loadPluginConfig();
    }

    /**
     * Initialize the daemon
     *
     * @return void
     */
    protected function initDaemon()
    {
        $daemon = Daemon::getInstance();
        if (!$daemon) {
            throw new \Exception(
                'Couldn\'t create instance, hopefully you got some error messages on console or in the log file.'
            );
        }

        if (isset($this->options['once'])) {
            $daemon->setRunOnce();
        }

        $daemon->setWorkingDir($this->options['working-dir']);
        $daemon->setProjectDir($this->options['project-dir']);
        $daemon->setStatusDir($this->options['status-dir']);
        $daemon->addProjectFiles($this->options['project-file']);

        return $daemon;
    }

    public static function execute()
    {
        try {
            $xinc = new self();
            $xinc->parseCliOptions();
            $xinc->initLogger();
            $xinc->initPlugins();
            $xinc->validateCliOptions();
            $daemon = $xinc->initDaemon();
            $daemon->run();
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }
}
