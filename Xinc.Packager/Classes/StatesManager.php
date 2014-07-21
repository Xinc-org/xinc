<?php
/**
 * Xinc - Cross integration and continous management.
 * This script belongs to the Xinc Packager framework.
 *
 * Usage and handling inspired by the CMS TYPO3.Neos / TYPO3.CMS which is LGPL 3+ licensed.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License, either version 3
 * of the License, or (at your option) any later version.
 *
 * PHP version 5
 *
 * @category Development
 * @package  Xinc.Packager
 * @author   Alexander Opitz <opitz.alexander@googlemail.com>
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU LGPL 3+
 * @link     http://code.google.com/p/xinc/
 */

namespace Xinc\Packager;

class StatesManager
{
    /**
     * @var bool Is true if we are in install mode.
     */
    private $installMode = false;

    /**
     * @var array Packages with their states.
     */
    private $packages = array();

    public function startInstallMode()
    {
        if ($this->installMode) {
            throw new \Exception('Already in install mode');
        }
        $this->readPackages();

        $this->installMode = true;
    }

    public function stopInstallMode()
    {
        if (!$this->installMode) {
            throw new \Exception('Not in install mode');
        }
        $this->writePackages();

        $this->installMode = false;
    }

    public function readPackages()
    {
        $statesPathAndFilename = $this->getStatesPathAndFilename();
        $configuration = file_exists($statesPathAndFilename) ? include($statesPathAndFilename) : array();

        if (!isset($configuration['version']) || $configuration['version'] < 4) {
            $this->packages = array();
        } else {
            $this->packages = $configuration['packages'];
        }
    }

    public function writePackages()
    {
        $states = array(
            'packages' => $this->packages,
            'version' => 4,
        );
        $fileDescription = "# PackageStates.php\n\n";
        $fileDescription .= "# This file is maintained by Xincs package management. Although you can edit it\n";
        $fileDescription .= "# manually, you should rather use the command line commands for maintaining packages.\n";
        $fileDescription .= "# Or with the composer commands.\n";
        $fileDescription .= "# If you remove this file you will lost the information about installed packages.\n";
        $fileDescription .= "# The file will be recreated in an empty state.\n";

        $packageStatesCode = "<?php\n$fileDescription\nreturn " . var_export($states, true) . ';';
        $statesPathAndFilename = $this->getStatesPathAndFilename();

        $result = @file_put_contents($statesPathAndFilename, $packageStatesCode);
        if ($result === FALSE) {
            throw new \Exception('Couldn\'t write PackageStates.php');
        }
    }

    public function getStatesPathAndFilename()
    {
        $path = realpath(__DIR__ . '/../../../../Configuration');
        if ($path === false) {
            @mkdir(__DIR__ . '/../../../../Configuration');
            $path = realpath(__DIR__ . '/../../../../Configuration');
            if ($path === false) {
                throw new \Exception('Configuration path not found');
            }
        }
        return $path . '/PackageStates.php';
    }

    /**
     * Returns if manager is in install mode
     *
     * @return bool True if install mode otherwise false.
     */
    public function isInstallMode()
    {
        return $this->installMode;
    }

    public function addPackage(Models\Package $package)
    {
        if (isset($this->packages[$package->getName()])) {
            throw new \Exception('Package ' . $package->getName() . ' already exists');
        }
        $this->packages[$package->getName()] = array(
            'manifestPath' => $package->getPathManifest(),
            'composerName' => $package->getComposerName(),
            'state' => $package->getState(),
            'packagePath' => $package->getPathPackage(),
            'classesPath' => $package->getPathClasses(),
        );
    }

    public function updatePackage(Models\Package $package)
    {
        if (!isset($this->packages[$package->getName()])) {
            throw new \Exception('Package ' . $package->getName() . ' does not exists');
        }
        $this->packages[$package->getName()] = array(
            'manifestPath' => $package->getPathManifest(),
            'composerName' => $package->getComposerName(),
            'state' => $package->getState(),
            'packagePath' => $package->getPathPackage(),
            'classesPath' => $package->getPathClasses(),
        );
    }

    public function removePackage($packageName)
    {
        if (!isset($this->packages[$package->getName()])) {
            throw new \Exception('Package ' . $package->getName() . ' does not exists');
        }
        unset($this->packages[$package->getName()]);
    }
}
