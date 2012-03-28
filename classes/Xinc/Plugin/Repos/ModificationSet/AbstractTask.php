<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet
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
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Plugin/Task/Base.php';

abstract class Xinc_Plugin_Repos_ModificationSet_AbstractTask
    extends Xinc_Plugin_Task_Base
{
    const STOPPED = -1;

    const FAILED = -2;

    const CHANGED = 1;

    const ERROR = 0;

    /**
     * abstract process of a modification set
     *
     * @param Xinc_Build_Interface $build The running build.
     *
     * @return void
     */
    public final function process(Xinc_Build_Interface $build)
    {
        $result = $this->checkModified($build);
        $build->info($result);
        if ( $result->getStatus() == Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED ) {
            $build->getProperties()->set('changeset', $result);
            $build->setStatus(Xinc_Build_Interface::PASSED);
        } else if ( $result->getStatus() === Xinc_Plugin_Repos_ModificationSet_AbstractTask::STOPPED ) {
            $build->setStatus(Xinc_Build_Interface::STOPPED);
        } else if ( $result->getStatus() === Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED ) {
            $build->setStatus(Xinc_Build_Interface::FAILED);
        } else if ( $result->getStatus() === Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR ) {
            $build->setStatus(Xinc_Build_Interface::STOPPED);
        } else {
            $build->setStatus(Xinc_Build_Interface::STOPPED);
        }
    }

    /**
     * Check if this modification set has been modified
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result
     */
    public abstract function checkModified(Xinc_Build_Interface $build);

    /**
     * Returns the slot of this task inside a build.
     *
     * @return integer The slot number.
     */
    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::PRE_PROCESS;
    }

    /**
     * Check necessary variables are set
     * 
     * @throws Xinc_Exception_MalformedConfig
     */
    public function validate()
    {
        try {
            return $this->validateTask();
        }
        catch(Exception $e){
            Xinc_Logger::getInstance()->error('Could not validate: '
                                             . $e->getMessage());
            return false;
        }
    }


    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public abstract function validateTask();
}