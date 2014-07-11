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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\CommandEvent;
use Composer\Script\Event;
use Composer\Script\PackageEvent;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer The running composer instance.
     */
    protected $composer;

    /**
     * @var IOInterface The IO control system.
     */
    protected $io;

    /**
     * Apply plugin modifications to composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // Register our own installer
        $composer->getInstallationManager()->addInstaller(
            new Installer($io, $composer)
        );

        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Returns an array of event names we want to listen to with the names of the callback functions.
     *
     * @return array The event names to listen with callback function.
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateAndInstall',
            ScriptEvents::POST_INSTALL_CMD => 'onPostUpdateAndInstall',
            ScriptEvents::PRE_PACKAGE_UPDATE => 'onPostPackageUpdateAndInstall',
            ScriptEvents::POST_PACKAGE_INSTALL => 'onPostPackageUpdateAndInstall',
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'onPreAutoloadDump',
        );
    }

    /**
     * Callback function for POST_UPDATE_CMD and POST_INSTALL_CMD.
     *
     * @param CommandEvent $event The occured event.
     * @return void
     */
    public function onPostUpdateAndInstall(CommandEvent $event)
    {
        if ($this->addAutoLoaderForPackager()) {
            \Xinc\Packager\Composer\Inside::postUpdateAndInstall($event);
        }
    }

    /**
     * Callback function for PRE_PACKAGE_UPDATE and POST_PACKAGE_INSTALL
     *
     * @param PackageEvent $event The occured event.
     * @return void
     */
    public function onPostPackageUpdateAndInstall(PackageEvent $event)
    {
        if ($this->addAutoLoaderForPackager()) {
            \Xinc\Packager\Composer\Inside::postPackageUpdateAndInstall($event);
        }
    }

    /**
     * Callback function for PRE_AUTOLOAD_DUMP.
     *
     * @param Event $event The occured event.
     * @return void
     */
    public function onPreAutoloadDump(Event $event)
    {
        if ($this->addAutoLoaderForPackager()) {
            \Xinc\Packager\Composer\Inside::preAutoloadDump($event);
        }
    }

    /**
     * Creates an AutoLoader for the Xinc.Packager package if it isn't already autoloadable and if package is installed.
     *
     * @return bool True if Xinc.Packager was available otherwise false.
     */
    protected function addAutoLoaderForPackager()
    {
        if (!class_exists('Xinc\\Packager\\Composer\\Inside')) {
            $packages = $this->composer->getRepositoryManager()->getLocalRepository()->findPackages('xinc/packager');
            if (count($packages) === 1) {
                $this->addAutoLoader(reset($packages));
                return true;
            }
        } else {
            return true;
        }

        $this->io->write('Xinc.Packager seams not installed yet');
        return false;
    }

    /**
     * Create an AutoLoader for given package.
     *
     * @param PackageInterface $package The package to create AutoLoader from.
     * @return void
     */
    protected function addAutoLoader(PackageInterface $package)
    {
        $path = $this->composer->getInstallationManager()->getInstallPath($package);

        $generator = $this->composer->getAutoloadGenerator();
        $map = $generator->parseAutoloads(
            array(array($package, $path)),
            new Package('dummy', '1.0.0.0', '1.0.0')
        );
        $classLoader = $generator->createLoader($map);
        $classLoader->register();
    }
}
