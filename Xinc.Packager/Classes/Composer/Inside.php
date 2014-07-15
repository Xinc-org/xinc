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
            !$operation instanceof \Composer\DependencyResolver\Operation\UpdateOperation) {
            throw new \Exception('JobType "' . $operation->getJobType() . '" is not supported.');
        }
        $composerPackage = ($operation->getJobType() === 'install') ? $operation->getPackage() : $operation->getTargetPackage();

        static::$statesManager->addPackageActivated(
            static::composerPackage2PackagerPackage($composerPackage)
        );

        $event->getIO()->write('postPackageUpdateAndInstall called: ' . $operation->getReason()->getPrettyString());
        $event->getIO()->write('job: ' . $operation->getJobType());
        $event->getIO()->write('package Name: ' . $composerPackage->getName());
        $event->getIO()->write('package Pretty: ' . $composerPackage->getPrettyName());
        $event->getIO()->write('package Names: ' . json_encode($composerPackage->getNames()));
        $event->getIO()->write('package Pretty String: ' . getPrettyString());
    }

    static public function preAutoloadDump(Event $event)
    {
        $event->getIO()->write('preAutoloadDump called');
    }

    static function composerPackage2PackagerPackage($composerPackage)
    {
        $package = new \Xinc\Packager\Models\Package();
        $package->setName($composerPackage->getPrettyName());
    }

//     protected function addAutoLoaderForPackagerOnInstallation(PackageEvent $event)
//     {
//         if ($event->getOperation()->getJobType() === 'install') {
//             $package = $event->getOperation()->getPackage();
//             if ($package->getName() === 'xinc/packager') {
//                 $this->addAutoLoader($package);
//             }
//         }
//     }
}
