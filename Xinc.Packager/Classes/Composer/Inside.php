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

namespace Xinc\Packager\Composer;

use Composer\Script\CommandEvent;
use Composer\Script\Event;
use Composer\Script\PackageEvent;
use Composer\Package\PackageInterface;

/**
 * The class handles composer events and should only be used from inside the composer. Otherwise the composer classes
 * won't be found. Calls are staticaly.
 */
class Inside
{
    /** @type \Xinc\Packager\StatesManager The manager over package states. */
    static private $statesManager = null;

    /**
     * Called from composer before update/install of packages generally.
     *
     * @param \Composer\Script\CommandEvent $event The event themself
     *
     * @return void
     */
    static public function preUpdateAndInstall(CommandEvent $event)
    {
        if (static::$statesManager === null) {
            static::$statesManager = new \Xinc\Packager\StatesManager();
        }
        static::$statesManager->startInstallMode();
    }

    /**
     * Called from composer after update/install of packages generally.
     *
     * @param \Composer\Script\CommandEvent $event The event themself
     *
     * @return void
     */
    static public function postUpdateAndInstall(CommandEvent $event)
    {
        if (static::$statesManager === null) {
            throw new \Exception('postUpdateAndInstall event without preUpdatePostAndInstall event.');
        }
        // @TODO We are called twice till yet, so test state.
        if (static::$statesManager->isInstallMode()) {
            static::$statesManager->stopInstallMode();
        }
    }

    /**
     * Called from composer after update/install/uninstall of a package.
     *
     * @param \Composer\Script\PackageEvent $event The event themself
     *
     * @return void
     */
    static public function postPackageUpdateAndInstall(PackageEvent $event)
    {
        if (static::$statesManager === null) {
            static::$statesManager = new \Xinc\Packager\StatesManager();
        }

        $needStoppingStatesManager = false;
        if (!static::$statesManager->isInstallMode()) {
            static::$statesManager->startInstallMode();
            $needStoppingStatesManager = true;
        }

        $operation = $event->getOperation();
        if (!$operation instanceof \Composer\DependencyResolver\Operation\InstallOperation &&
            !$operation instanceof \Composer\DependencyResolver\Operation\UninstallOperation &&
            !$operation instanceof \Composer\DependencyResolver\Operation\UpdateOperation) {
            throw new \Exception('JobType "' . $operation->getJobType() . '" is not supported.');
        }

        switch ($operation->getJobType()) {
            case 'install':
                $composerPackage = $operation->getPackage();
                if ($composerPackage->getType() === 'xinc-application-package') {
                    $packege = static::composerPackage2PackagerPackage($composerPackage);
                    $packege->setState('active');
                    try {
                        static::$statesManager->addPackage($packege);
                    } catch (\Exception $e) {
                        // @TODO: Catch exception as we may called twice
                    }
                }
                break;
            case 'update':
                $composerPackage = $operation->getTargetPackage();
                if ($composerPackage->getType() === 'xinc-application-package') {
                    static::$statesManager->updatePackage(
                        static::composerPackage2PackagerPackage($composerPackage)
                    );
                }
                break;
            case 'uninstall':
                $composerPackage = $operation->getPackage();
                if ($composerPackage->getType() === 'xinc-application-package') {
                    static::$statesManager->removePackage(
                        static::composerPackage2PackagerPackageName($composerPackage)
                    );
                }
                break;
        }

        if ($needStoppingStatesManager) {
            static::$statesManager->stopInstallMode();
        }
    }

    /**
     * Called from composer before starting the generation of the autoloader files.
     *
     * @param \Composer\Script\Event $event The event themself
     *
     * @return void
     */
    static public function preAutoloadDump(Event $event)
    {
        $event->getIO()->write('preAutoloadDump called');
    }

    /**
     * Converts a composer package object to a Xinc.Packager package object.
     *
     * @param \Composer\Package\PackageInterface $composerPackage The composer package to convert
     *
     * @return \Xinc\Packager\Models\Package The Xinc.Packager package model.
     */
    static function composerPackage2PackagerPackage(PackageInterface $composerPackage)
    {
        $package = new \Xinc\Packager\Models\Package();
        $package->setName(static::composerPackage2PackagerPackageName($composerPackage));
        $package->setComposerName($composerPackage->getPrettyName());

        return $package;
    }


    /**
     * Converts a composer package object to a Xinc.Packager package name.
     *
     * @param \Composer\Package\PackageInterface $composerPackage The composer package to convert
     *
     * @return string The name in Xinc.Packager format
     */
    static function composerPackage2PackagerPackageName(PackageInterface $composerPackage)
    {
        $nameParts = preg_split('/\//', $composerPackage->getPrettyName(), -1, PREG_SPLIT_NO_EMPTY);
        $nameParts = array_map('ucfirst', $nameParts);
        return implode('.', $nameParts);
    }
}
