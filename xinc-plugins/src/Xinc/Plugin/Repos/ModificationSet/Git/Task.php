<?php
/**
 * Xinc - Continuous Integration.
 * Provides git support.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet.Git
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2011-2012 Alexander Opitz, Leipzig
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

require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';

class Xinc_Plugin_Repos_ModificationSet_Git_Task extends Xinc_Plugin_Repos_ModificationSet_AbstractTask
{
    /**
     * @var string Directory containing the Git project.
     */
    private $strDirectory = '.';

    /**
     * @var boolean Update repository if change detected.
     */
    private $bUpdate = false;

    /**
     * @var string The remote repository to clone from.
     */
    private $strRepository = '';

    /**
     * Returns name of task.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return 'git';
    }

    /**
     * Sets the git checkout directory.
     *
     * @param string $strDirectory Directory for git checkout.
     *
     * @return void
     */
    public function setDirectory($strDirectory)
    {
        $this->strDirectory = (string)$strDirectory;
    }

    /**
     * Gets the git checkout directory.
     *
     * @return string Directory which was set.
     */
    public function getDirectory()
    {
        return $this->strDirectory;
    }

    /**
     * Sets the remote repository.
     *
     * @param string $strRepository The remote repository.
     *
     * @return void
     */
    public function setRepository($strRepository)
    {
        $this->strRepository = (string)$strRepository;
    }

    /**
     * Tells whether to update the working copy directly here or not
     *
     * @param string $strUpdate "true" or "1" as string to set update otherwise
     *  it is interpreted as false.
     *
     * @return void
     */
    public function setUpdate($strUpdate)
    {
        $this->bUpdate = in_array($strUpdate, array('true', '1')) ? true:false;
    }

    /**
     * Get if git should be automaticaly updated.
     *
     * @return boolean True if git repos should be updated.
     */
    public function getUpdate()
    {
        return $this->bUpdate;
    }

    /**
     * Check if this modification set has been modified
     *
     * @param Xinc_Build_Interface $build The running build.
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result The result of the check.
     */
    public function checkModified(Xinc_Build_Interface $build)
    {
        return $this->plugin->checkModified($build, $this);
    }

    /**
     * Validates if a task can run by checking configs, directories and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validateTask()
    {
        if (!class_exists('VersionControl_Git')) {
            throw new Xinc_Exception_MalformedConfig(
                'PEAR::VersionControl_Git doesn\'t exists. You need to install it to use this task.'
            );
        }
        if (!isset($this->strDirectory)) {
            throw new Xinc_Exception_MalformedConfig(
                'Element modificationSet/git - required attribute "directory" is not set.'
            );
        }

        return true;
    }
}
