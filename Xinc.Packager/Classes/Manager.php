<?php
/**
 * Xinc - Cross integration and continous management.
 * This script belongs to the Xinc Packager framework.
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

class Manager
{
    /**
     * @var string
     */
    private $composerBinDir = '';

    /**
     * @var string
     */
    private $composerJsonDir = '';

    /**
     * @var string
     */
    private $packageDir = '';

    /**
     * Constructor
     *
     * @param string $composerBinDir Directory where composer executable can be found.
     * @param string $composerJsonDir Directory where composer.json can be found.
     * @param string $packageDir Directory where PackageStates.php can be found.
     */
    public function __construct($composerBinDir, $composerJsonDir, $packageDir)
    {
        $this->composerBinDir = $composerBinDir;
        $this->composerJsonDir = $composerJsonDir;
        $this->packageDir = $packageDir;
    }

    /**
     * Install a package with composer.
     *
     * @return void
     */
    public function install($name) {
        $bridge = new ComposerBridge($this->composerBinDir, $this->composerJsonDir);
        if ($bridge->isInstalled($name)) {
            throw new Exception('Package "' . $name . '" already installed.');
        }
        throw new Exception('Not yet implemented. Use "composer require ' . $name . '"');
    }

    /**
     * Remove a package with composer.
     *
     * @return void
     */
    public function deinstall($name) {
        throw new Exception('Not yet implemented.');
    }

    /**
     * Activates a package in PackageStates.php and composers ClassLoader.
     *
     * @return void
     */
    public function activate($name) {
        throw new Exception('Not yet implemented.');
    }

    /**
     * Deactivates a package in PackageStates.php and composers ClassLoader.
     *
     * @return void
     */
    public function deactivate($name) {
        throw new Exception('Not yet implemented.');
    }
}
