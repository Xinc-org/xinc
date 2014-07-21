<?php
/**
 * Xinc - Cross integration and continous management.
 * This script belongs to the Xinc Packager.
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

namespace Xinc\Packager\Models;

class Package
{
    private $name;

    private $composerName;

    private $pathManifest;

    private $pathPackage;

    private $pathClasses;

    private $state;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getComposerName()
    {
        return $this->composerName;
    }

    public function setComposerName($composerName)
    {
        $this->composerName = $composerName;
    }

    public function getPathManifest()
    {
        return $this->pathManifest;
    }

    public function setPathManifest($pathManifest)
    {
        $this->pathManifest = $pathManifest;
    }

    public function getPathPackage()
    {
        return $this->pathPackage;
    }

    public function setPathPackage($pathPackage)
    {
        $this->pathPackage = $pathPackage;
    }

    public function getPathClasses()
    {
        return $this->pathClasses;
    }

    public function setPathClasses($pathClasses)
    {
        $this->pathClasses = $pathClasses;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setStateActive()
    {
        $this->state = 'active';
    }

    public function setStateInactive()
    {
        $this->state = 'inactive';
    }
}
