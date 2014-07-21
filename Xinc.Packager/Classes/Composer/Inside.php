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

namespace Xinc\Packager\Composer;

use Composer\Script\CommandEvent;
use Composer\Script\Event;
use Composer\Script\PackageEvent;

class Inside
{
    static private $statesManager = null;

    static public function preUpdateAndInstall(CommandEvent $event)
    {
        if (static::$statesManager === null) {
            static::$statesManager = new \Xinc\Packager\StatesManager();
        }
        static::$statesManager->startInstallMode();
    }

    static public function postUpdateAndInstall(CommandEvent $event)
    {
        if (static::$statesManager === null) {
            throw new \Exception('postUpdateAndInstall event without preUpdatePostAndInstall event.');
        }
        // @TODO We are called twice till yet, so test state.
        if (static::$statesManager->inInstallMode()) {
            static::$statesManager->stopInstallMode();
        }
    }

    static public function postPackageUpdateAndInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();
        if (!$operation instanceof \Composer\DependencyResolver\Operation\InstallOperation &&
            !$operation instanceof \Composer\DependencyResolver\Operation\UninstallOperation &&
            !$operation instanceof \Composer\DependencyResolver\Operation\UpdateOperation) {
            throw new \Exception('JobType "' . $operation->getJobType() . '" is not supported.');
        }

        switch ($operation->getJobType()) {
            case 'install':
                $composerPackage = $operation->getPackage();
                $packege = static::composerPackage2PackagerPackage($composerPackage);
                $package->setState('active');
                try {
                    static::$statesManager->addPackage($packege);
                } catch (\Exception $e) {
                    // @TODO: Catch exception as we may called twice
                }
                break;
            case 'update':
                $composerPackage = $operation->getTargetPackage();
                static::$statesManager->updatePackage(
                    static::composerPackage2PackagerPackage($composerPackage)
                );
                break;
            case 'uninstall':
                $composerPackage = $operation->getPackage();
                static::$statesManager->removePackage(
                    static::composerPackage2PackagerPackageName($composerPackage)
                );
                break;
        }
        $composerPackage = ($operation->getJobType() === 'update') ? $operation->getPackage() : $operation->getTargetPackage();

        static::$statesManager->addPackageActivated(
            static::composerPackage2PackagerPackage($composerPackage)
        );
    }

    static public function preAutoloadDump(Event $event)
    {
        $event->getIO()->write('preAutoloadDump called');
    }

    static function composerPackage2PackagerPackage($composerPackage)
    {
        $package = new \Xinc\Packager\Models\Package();
        $package->setName(static::composerPackage2PackagerPackageName($composerPackage));
        $package->setComposerName($composerPackage->getPrettyName());
    }

    static function composerPackage2PackagerPackageName($composerPackage)
    {
        return str_replace('/', '.', $composerPackage->getPrettyName());
    }
}
