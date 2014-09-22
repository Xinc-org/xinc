<?php
/**
 * Xinc - Cross integration and continous management.
 * This script belongs to the Xinc package "Xinc.Packager".
 *
 * It is free software; you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License, either version 3 of the License, or (at your option) any later version.
 *
 * @package Xinc.Packager
 * @author  Alexander Opitz <opitz.alexander@googlemail.com>
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL 3+
 * @see     http://code.google.com/p/xinc/
 */

namespace Xinc\Packager;

/**
 * This is the entry class for command line interface operations.
 */
class Cli
{
    /** @type array Holding the merged cli parameters. */
    private $options = array();

    /**
     * Handle command line arguments.
     *
     * @return void
     */
    protected function parseOptions()
    {
        $workingDir = dirname($_SERVER['argv'][0]);

        $opts = getopt(
            'p:c:j:i:u:a:d:',
            array(
                'package-dir:',
                'composer-bin-dir:',
                'composer-json-dir:',
                'install:',
                'uninstall:',
                'activate:',
                'deactivate:',
                'version',
                'help',
            )
        );

        if (isset($opts['version'])) {
            $this->showVersion();
            exit();
        }

        if (isset($opts['help'])) {
            $this->showHelp();
            exit();
        }

        $this->options = $this->mergeOpts(
            $opts,
            array(
                'p' => 'package-dir',
                'c' => 'composer-bin-dir',
                'j' => 'composer-json-dir',
                'i' => 'install',
                'u' => 'uninstall',
                'a' => 'activate',
                'd' => 'deactivate',
            ),
            array (
                'package-dir' => $this->getPackageDir(),
                'composer-bin-dir' => $this->getComposerBinDir(),
                'composer-json-dir' => $this->getComposerJsonDir(),
            )
        );
    }

    /**
     * Returns path to the directory where configuration should be (PackageStates.php) but not tested.
     *
     * @return string Path to configuration.
     */
    protected function getPackageDir()
    {
        return __DIR__ . '/../../../../Configuration';
    }

    /**
     * Returns path to the directory where the composer executable should be.
     *
     * @return string Path to composer executable.
     */
    protected function getComposerBinDir()
    {
        return __DIR__ . '/../../../..';
    }

    /**
     * Returns path to the directory where the composer.json configuration should be.
     *
     * @return string Path to composer json.
     */
    protected function getComposerJsonDir()
    {
        return __DIR__ . '/../../../..';
    }

    /**
     * Validates the existence of given directory options (package-dir, composer-dir)
     *
     * @throws \Xinc\Packager\Exception
     */
    protected function validateFileDirectoryOptions()
    {
        $this->options['package-dir'] = $this->checkDirectory($this->options['package-dir']);
        $this->options['composer-bin-dir'] = $this->checkDirectory($this->options['composer-bin-dir']);
        $this->options['composer-json-dir'] = $this->checkDirectory($this->options['composer-json-dir']);
    }

    /**
     * Validates that given options do not collide
     *
     * @throws \Xinc\Packager\Exception
     */
    protected function validateManagementOptions()
    {
        $managementOptionsSet = 0;
        if (isset($this->options['install'])) {
            $managementOptionsSet++;
        }
        if (isset($this->options['uninstall'])) {
            $managementOptionsSet++;
        }
        if (isset($this->options['activate'])) {
            $managementOptionsSet++;
        }
        if (isset($this->options['deactivate'])) {
            $managementOptionsSet++;
        }
        if ($managementOptionsSet === 0) {
            throw new \Xinc\Packager\Exception('No management option set.');
        }
        if ($managementOptionsSet > 1) {
            throw new \Xinc\Packager\Exception('Too much management options set.');
        }
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
     * @param string $directory Directory to check for.
     *
     * @return string The realpath of given directory.
     * @throws \Xinc\Packager\Exception
     */
    protected function checkDirectory($directory)
    {
        if (!is_dir($directory)) {
            throw new \Xinc\Packager\Exception('Directory does not exists or no permissions: ' . $directory);
        } elseif (!is_readable($directory)) {
            throw new \Xinc\Packager\Exception('Can\'t read directory: ' .$directory);
        }
        $directory = realpath($directory);
        if ($directory === false) {
            throw new \Xinc\Packager\Exception('No permissions for directory: ' . $directory);
        }
        return realpath($directory);
    }

    /**
     * Prints help message, describing different parameters to run packager.
     *
     * @return void
     */
    protected function showHelp()
    {
        echo 'Usage: xinc-packager [switches]' . "\n\n";

        echo '  -p --package-dir=<dir>       The directory to the package configuration.' . "\n"
            . '  -c --composer-bin-dir=<dir>  The directory to composer executable.' . "\n"
            . '  -j --composer-json-dir=<dir> The directory to composer executable.' . "\n"
            . '  -i --install=<package>       The package to install.' . "\n"
            . '  -u --uninstall=<package>     The package to remove.' . "\n"
            . '  -a --activate=<package>      The package to activate.' . "\n"
            . '  -d --deactivate=<package>    The package to deactivate.' . "\n"
            . '  --version                    Prints the version of Xinc.' . "\n"
            . '  -h --help                    Prints this help message.' . "\n";
    }

    /**
     * Executes the given parameter on the Manager.
     *
     * @return void
     */
    protected function executeManagement()
    {
        $manager = new Manager(
            $this->options['composer-bin-dir'],
            $this->options['composer-json-dir'],
            $this->options['package-dir']
        );

        if (isset($this->options['install'])) {
            $manager->install($this->options['install']);
        }
        if (isset($this->options['deinstall'])) {
            $manager->deinstall($this->options['deinstall']);
        }
        if (isset($this->options['activate'])) {
            $manager->activate($this->options['activate']);
        }
        if (isset($this->options['deactivate'])) {
            $manager->deactivate($this->options['deactivate']);
        }
    }

    /**
     * Execution of this cli part. Outputs error message and exits with status code 1.
     *
     * @return void
     */
    public static function execute()
    {
        try {
            $cli = new self();
            $cli->parseOptions();
            $cli->validateFileDirectoryOptions();
            $cli->validateManagementOptions();
            $cli->executeManagement();
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            exit(1);
        }
    }
}
