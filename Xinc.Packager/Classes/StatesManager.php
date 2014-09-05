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
 * Management over the PackageStates.php
 */
class StatesManager
{
    /** @type bool Is true if we are in install mode. */
    private $installMode = false;

    /** @type array Packages with their states. */
    private $packages = array();

    /** @type Persistence */
    private $persistence = null;

    public function startInstallMode()
    {
        if ($this->installMode) {
            throw new \Exception('Already in install mode');
        }
        $this->injectPersistence();
        $this->packages = $this->persistence->getPackages();

        $this->installMode = true;
    }

    public function stopInstallMode()
    {
        if (!$this->installMode) {
            throw new \Exception('Not in install mode');
        }
        $this->injectPersistence();
        $this->persistence->writePackages($this->packages);

        $this->installMode = false;
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

    private function injectPersistence()
    {
        if ($this->persistence === null) {
            $this->persistence = new Persistence();
        }
    }
}
