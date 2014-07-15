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

class StatesManager
{
    public function startInstallMode()
    {

    }

    public function stopInstallMode()
    {

    }

    public function isInstallMode()
    {

    }

    public function addPackageActivated(Models\Package $package)
    {
        ECHO 'YO package "' . $package->getName() . '" will be added.';
    }
}
