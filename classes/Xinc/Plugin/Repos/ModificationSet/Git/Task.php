<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Provides git support.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet.Git
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2011 Alexander Opitz, Leipzig
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

class Xinc_Plugin_Repos_ModificationSet_Git_Task
    extends Xinc_Plugin_Repos_ModificationSet_AbstractTask
{
    /**
     * @var string Directory containing the Git project.
     */
    private $strPath = '.';

    /**
     * @var boolean Update repository if change detected.
     */
    private $bUpdate = false;

    private $_username = null;

    private $_password = null;

    private $_property;

    /**
     * @var VersionControl_Git The git object.
     */
    private $git = null;

    public function getName()
    {
        return 'git';
    }

    public function registerTask(Xinc_Plugin_Task_Interface $task)
    {
        $this->_subtasks[]=$task;
    }

    /**
     * Sets the svn checkout directory.
     *
     * @param string
     */
    public function setDirectory($directory)
    {
        $this->strPath = (string)$directory;
    }

    /**
     * sets the name of the property, which will be set to
     * TRUE in case a modification was detected
     *
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->_property = (string) $property;
    }

    /**
     * Sets the username for the svn commands
     *
     * @param string
     */
    public function setUsername($username)
    {
        $this->_username = (string)$username;
    }

    /**
     * Sets the password for the svn commands
     *
     * @param string
     */
    public function setPassword($password)
    {
        $this->_password = (string)$password;
    }

    /**
     * Tells whether to update the working copy directly here or not
     *
     * @param string $update
     */
    public function setUpdate($strUpdate)
    {
        $this->bUpdate = in_array($strUpdate, array('true', '1')) ? true:false;
    }

    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::PRE_PROCESS;
    }

    public function checkModified(Xinc_Build_Interface $build)
    {
        $res = new Xinc_Plugin_Repos_ModificationSet_Result();

        try {
            $this->git = new VersionControl_Git($this->strPath);
            $strBranch = $this->git->getCurrentBranch();

            $strRemoteHash = $this->getRemoteHash($strBranch);
            $strLocalHash = $this->getLocalHash($strBranch);
        } catch(Exception $e) {
            $build->error('Test of GIT Repos failed: ' . $e->getMessage());
            $res->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED);
            return $res;
        }

        $res->setRemoteRevision($strRemoteHash);
        $res->setLocalRevision($strLocalHash);

var_dump($strRemoteHash);
var_dump($strLocalHash);

        if ($strRemoteHash !== $strLocalHash) {
            $this->fetch();
            $this->getModifiedFiles($build, $res);

            if ($this->bUpdate) {
                try {
                    $this->update();
                } catch(Exception $e) {
                    $build->error('Update of GIT local failed: ' . $e->getMessage());
                    $res->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED);
                    return $res;
                }
            }
            $res->setStatus(Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED);
            //var_dump($this->git->getCommits());
        }

        return $res;
    }

    public function validateTask()
    {
        if (!isset($this->strPath)) {
            throw new Xinc_Exception_MalformedConfig('Element modificationSet/git'
                                                    . ' - required attribute '
                                                    . '\'directory\' is not set');
        }

        $this->git = new VersionControl_Git($this->strPath);

        try {
            $this->git->getHeadCommits();
        } catch (Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
/*        $file = $this->_directory;
        $file2 = Xinc::getInstance()->getWorkingDir() . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($file) && !file_exists($file2)) {
            Xinc_Logger::getInstance()->error('Directory '.$file2.' does not exist');
            return false;
        } else if (file_exists($file2)) {
            $this->_directory = $file2;
            //Xinc_Logger::getInstance()->error("Directory $file2 does not exist");
            //return false;
        }
        //return false;*/
        return true;
    }

    protected function update()
    {
        $command = $this->git->getCommand('pull')
            ->setOption('ff-only')
            ->setOption('stat');
        try {
            $strResult = $command->execute();
        } catch (VersionControl_Git_Exception $e) {
            throw new Xinc_Exception_ModificationSet(
                'GIT update failed: ' . $e->getMessage(),
                0, $e
            );
        }
    }

    protected function fetch()
    {
        $command = $this->git->getCommand('fetch')
            ->setOption('no-recurse-submodules');
        try {
            $strResult = $command->execute();
        } catch (VersionControl_Git_Exception $e) {
            throw new Xinc_Exception_ModificationSet(
                'GIT fetch failed: ' . $e->getMessage(),
                0, $e
            );
        }
    }

/*      git ls-remote -heads
        git ls-remote -h .
        git log --pretty=format:'%H' -1*/
    protected function getRemoteHash($strBranchName)
    {
        $command = $this->git->getCommand('ls-remote')
            ->setOption('heads');
        try {
            $strResult = $command->execute();
        } catch (VersionControl_Git_Exception $e) {
            throw new Xinc_Exception_ModificationSet(
                'GIT get remote hash failed: ' . $e->getMessage(),
                0, $e
            );
        }
        $arCommandLines = explode(PHP_EOL, trim($strResult));
        foreach($arCommandLines as $strCommandLine) {
            $arParts = explode("\t", $strCommandLine);
            if ($arParts[1] === 'refs/heads/' . $strBranchName) {
                return $arParts[0];
            }
        }

        throw new Xinc_Exception_ModificationSet(
            'Branch "' . $strBranchName . '" not exists in remote git repository.'
        );
    }

    protected function getLocalHash($strBranchName)
    {
        try {
            $arHashs = $this->git->getHeadCommits();
        } catch (VersionControl_Git_Exception $e) {
            throw new Xinc_Exception_ModificationSet(
                'GIT get local hash failed: ' . $e->getMessage(),
                0, $e
            );
        }
        if (isset($arHashs[$strBranchName])) {
            return $arHashs[$strBranchName];
        }

        throw new Xinc_Exception_ModificationSet(
            'Branch "' . $strBranchName . '" not exists in local git repository.'
        );
    }

    protected function getModifiedFiles(
        Xinc_Build_Interface $build,
        Xinc_Plugin_Repos_ModificationSet_Result $res
    ) {
        $command = $this->git->getCommand('diff')
            ->setOption('name-status')
            ->addArgument($res->getLocalRevision())
            ->addArgument($res->getRemoteRevision());
        try {
            $strResult = $command->execute();
        } catch (VersionControl_Git_Exception $e) {
            throw new Xinc_Exception_ModificationSet(
                'GIT get version diff failed: ' . $e->getMessage(),
                0, $e
            );
        }
        
        $arCommandLines = explode(PHP_EOL, trim($strResult));
        foreach($arCommandLines as $strCommandLine) {
            list($strStatus, $strFile) = explode("\t", $strCommandLine);
            switch($strStatus) {
            case 'M': //Modified
            case 'R': //Renamed
            case 'T': //Type changed
                $res->addUpdatedResource($strFile, $author);
                break;
            case 'D': //Deleted
                $res->addDeletedResource($strFile, $author);
                break;
            case 'A': //Added
            case 'C': //Copied
                $res->addNewResource($strFile, $author);
                break;
            case 'U': // Unmerged
            case 'X': // Unknown
            case 'B': // Broken pairing
            default:
                $res->addConflictResource($strFile, $author);
                break;
            }
        }
    }
}