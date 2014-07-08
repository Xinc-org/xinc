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

class ComposerBridge
{
    public function __construct($binDir, $jsonDir)
    {
        \Phar::loadPhar($binDir . '/composer.phar', 'composer.phar');
        require 'phar://composer.phar/src/bootstrap.php';

        $this->io = new \Composer\IO\NullIO();
        $this->composer = \Composer\Factory::create($this->io, $jsonDir . '/composer.json');

        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $jsonFile = new \Composer\Json\JsonFile($jsonDir . '/' . $vendorDir . '/composer/installed.json');

        $this->composer->getRepositoryManager()->setLocalRepository(new \Composer\Repository\InstalledFilesystemRepository($jsonFile));
    }

    public function isInstalled($package)
    {
        if (count($this->composer->getRepositoryManager()->getLocalRepository()->findPackages($package))) {
            return true;
        } else {
            return false;
        }
    }
}
