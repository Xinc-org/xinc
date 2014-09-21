<?php
/**
 * Xinc - Cross integration and continous management.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License, either version 3
 * of the License, or (at your option) any later version.
 *
 * PHP version 5
 *
 * @category Development
 * @package  Xinc.Composer
 * @author   Alexander Opitz <opitz.alexander@googlemail.com>
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU LGPL 3+
 * @link     http://code.google.com/p/xinc/
 */

namespace Xinc\Composer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;

class Installer extends LibraryInstaller
{
    /**
     * Initializes the Installer.
     *
     * @param IOInterface $io
     * @param Composer $composer
     * @param string $type
     * @param Filesystem $filesystem
     */
    public function __construct(
        IOInterface $io,
        Composer $composer,
        $type = 'xinc-application-package',
        Filesystem $filesystem = null
    ) {
        parent::__construct($io, $composer, $type, $filesystem);
    }

    /**
     * Returns the installation path of a package
     *
     * @param PackageInterface $package Package to install.
     * @return string path
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($package->getType() === $this->type) {
            return 'Packages/Applications/' . self::getPathName($package);
        }

        throw new \InvalidArgumentException(
            'Sorry the package type of this package is not supported.'
        );
    }
    
    /**
     * Returns the path name for given package.
     *
     * @param PackageInterface $package Package to install.
     * @return string path name.
     */
    public static function getPathName(PackageInterface $package)
    {
        $autoload = $package->getAutoload();
        if (isset($autoload['psr-4']) && is_array($autoload['psr-4'])) {
            $namespace = key($autoload['psr-4']);
        } elseif (isset($autoload['psr-0']) && is_array($autoload['psr-0'])) {
            $namespace = key($autoload['psr-0']);
        } else {
            throw new \InvalidArgumentException(
                'Your Package needs to support PSR-4 or at least PSR-0.'
            );
        }
        return rtrim(str_replace('\\', '.', $namespace), '.');
    }    
}
