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
 * Read/Write PackageStates.php
 */
class Persistence
{
    /** @type array Packages with their states for caching. */
    private $packages = null;

    public function getPackages()
    {
        if ($this->packages === null) {
            $this->readPackages();
        }

        return $this->packages;
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

    public function writePackages($packages)
    {
        $this->packages = $packages;
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
        if ($result === false) {
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
}
