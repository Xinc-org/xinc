<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Phing
 * @author    Arno Schneider <username@example.org>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://xincplus.sourceforge.net
 */

require_once 'phing/BuildEvent.php';
require_once 'phing/BuildListener.php';

class Listener implements BuildListener
{
    const MSG_DEBUG = 4;

    const MSG_VERBOSE = 3;

    const MSG_INFO = 2;

    const MSG_WARN = 1;

    const MSG_ERR = 0;

    private $_mapSeverity = array(self::MSG_DEBUG   => Xinc_Logger::LOG_LEVEL_DEBUG,
                                  self::MSG_VERBOSE => Xinc_Logger::LOG_LEVEL_DEBUG,
                                  self::MSG_INFO => Xinc_Logger::LOG_LEVEL_INFO,
                                  self::MSG_WARN => Xinc_Logger::LOG_LEVEL_WARN,
                                  self::MSG_ERR => Xinc_Logger::LOG_LEVEL_ERROR);

    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent The BuildEvent
     */
    public function buildStarted(BuildEvent $event)
    {
    }

    /**
     * Fired after the last target has finished.
     *
     * @param BuildEvent The BuildEvent
     *
     * @see BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event)
    {
    }

    /**
     * Fired when a target is started.
     *
     * @param BuildEvent The BuildEvent
     *
     * @see BuildEvent::getTarget()
     */
    public function targetStarted(BuildEvent $event)
    {
    }

    /**
     * Fired when a target has finished.
     *
     * @param BuildEvent The BuildEvent
     *
     * @see BuildEvent#getException()
     */
    public function targetFinished(BuildEvent $event)
    {
    }

    /**
     * Fired when a task is started.
     *
     * @param BuildEvent The BuildEvent
     *
     * @see BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task has finished.
     *
     *  @param BuildEvent The BuildEvent
     *
     *  @see BuildEvent::getException()
     */
    public function taskFinished(BuildEvent $event)
    {
    }

    /**
     *  Fired whenever a message is logged.
     *
     *  @param BuildEvent The BuildEvent
     *
     *  @see BuildEvent::getMessage()
     */
    public function messageLogged(BuildEvent $event)
    {
        $logger = Xinc_Logger::getInstance();
        /**
         * write to a temporary logfile
         * - which will be read afterwards and the logentries will
         * - be used to determine the status of the build
         */
        switch ($event->getPriority()) {
            case self::MSG_DEBUG:
            case self::MSG_VERBOSE:
                $logger->debug('[phing] '.$event->getMessage());
                break;
            case self::MSG_INFO:
                $logger->info('[phing] '.$event->getMessage());
                break;
            case self::MSG_WARN:
                $logger->warn('[phing] '.$event->getMessage());
                break;
            case self::MSG_ERR:
                $logger->error('[phing] '.$event->getMessage());
                Xinc::getCurrentBuild()->setStatus(Xinc_Build_Interface::FAILED);
                break;
        }
        $exception = $event->getException();
        if ($exception != null) {
            $logger->error('[phing] ' . $exception->getMessage());
            Xinc::getCurrentBuild()->setStatus(Xinc_Build_Interface::FAILED);
        }
    }
}