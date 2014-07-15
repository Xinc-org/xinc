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
    static public function postUpdateAndInstall(CommandEvent $event)
    {
        $event->getIO()->write('postUpdateAndInstall called');
    }

    static public function postPackageUpdateAndInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();
        if (!$operation instanceof \Composer\DependencyResolver\Operation\InstallOperation &&
            !$operation instanceof \Composer\DependencyResolver\Operation\UpdateOperation) {
            throw new \Exception('JobType "' . $operation->getJobType() . '" is not supported.');
        }
        $package = ($operation->getJobType() === 'install') ? $operation->getPackage() : $operation->getTargetPackage();

        $event->getIO()->write('postPackageUpdateAndInstall called: ' . $operation->getReason());
        $event->getIO()->write('job: ' . $operation->getJobType());
        $event->getIO()->write('package Name: ' . $package->getName());
        $event->getIO()->write('package Pretty: ' . $package->getPrettyName());
        $event->getIO()->write('package Names: ' . json_encode($package->getNames()));

        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    static public function preAutoloadDump(Event $event)
    {
        $event->getIO()->write('preAutoloadDump called');
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
