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

class Xinc_Plugin_Repos_ModificationSet_Git_Task
    extends Xinc_Plugin_Repos_ModificationSet_AbstractTask
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
     * @var VersionControl_Git The git object.
     */
    private $git = null;

    /**
     * Returns name of Task.
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
     * @param string
     */
    public function setDirectory($strDirectory)
    {
        $this->strDirectory = (string)$strDirectory;
    }

    /**
     * Sets the remote repository.
     *
     * @param string
     */
    public function setRepository($strRepository)
    {
        $this->strRepository = (string)$strRepository;
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

    /**
     * Check if this modification set has been modified
     *
     * @param Xinc_Build_Interface $build The running build.
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result The result of the check.
     */
    public function checkModified(Xinc_Build_Interface $build)
    {
        $res = new Xinc_Plugin_Repos_ModificationSet_Result();

        try {
            $this->git = new VersionControl_Git($this->strDirectory);
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
            $this->getChangeLog($build, $res);

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
        if (!class_exists('VersionControl_Git')) {
            throw new Xinc_Exception_MalformedConfig(
                'PEAR::VersionControl_Git doesn\'t exists.'
                . 'You need to install it to use this task. '
            );
        }
        if (!isset($this->strDirectory)) {
            throw new Xinc_Exception_MalformedConfig(
                'Element modificationSet/git - required attribute '
                . '\'directory\' is not set'
            );
        }

        $this->git = new VersionControl_Git($this->strDirectory);

        try {
            $this->git->getHeadCommits();
        } catch (Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
        /*
        $file = $this->_directory;
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
            ->addArgument(
                $res->getLocalRevision() . '..' . $res->getRemoteRevision()
            );
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
            // @TODO We need to diff from rev to rev so we can add Author Name.
            $strAuthor = null;

            list($strStatus, $strFile) = explode("\t", $strCommandLine);
            switch($strStatus) {
            case 'M': //Modified
            case 'R': //Renamed
            case 'T': //Type changed
                $res->addUpdatedResource($strFile, $strAuthor);
                break;
            case 'D': //Deleted
                $res->addDeletedResource($strFile, $strAuthor);
                break;
            case 'A': //Added
            case 'C': //Copied
                $res->addNewResource($strFile, $strAuthor);
                break;
            case 'U': // Unmerged
            case 'X': // Unknown
            case 'B': // Broken pairing
            default:
                $res->addConflictResource($strFile, $strAuthor);
                break;
            }
        }
    }

    protected function getChangeLog(
        Xinc_Build_Interface $build, 
        Xinc_Plugin_Repos_ModificationSet_Result $res
    ) {
        $command = $this->git->getCommand('log')
            ->setOption('pretty', 'H:%H%nA:%aN%nD:%aD%nM:%s')
            ->addArgument(
                $res->getLocalRevision() . '..' . $res->getRemoteRevision()
            );
        try {
            $strResult = $command->execute();
        } catch (VersionControl_Git_Exception $e) {
            throw new Xinc_Exception_ModificationSet(
                'GIT get log failed: ' . $e->getMessage(),
                0, $e
            );
        }
        $arCommandLines = explode(PHP_EOL, trim($strResult));
        while (count($arCommandLines)) {
            $strHash    = $this->getLogEntry('H', $arCommandLines);
            $strAuthor  = $this->getLogEntry('A', $arCommandLines);
            $strDate    = $this->getLogEntry('D', $arCommandLines);
            $strMessage = $this->getLogEntry('M', $arCommandLines);
            
            $res->addLogMessage($strHash, $strDate, $strAuthor, $strMessage);
        }
    }

    protected function getLogEntry($strType, array &$arLogEntries)
    {
        $strLogEntry = array_shift($arLogEntries);
        if ($strType . ':' !== substr($strLogEntry, 0, 2)) {
            throw new Xinc_Exception_ModificationSet(
                'GIT log: Cannot parse log line'
            );
        }
        $strLogEntry = substr($strLogEntry, 2);
        switch ($strType) {
        case 'D':
            $nTime = strtotime($strLogEntry);
            if (false === $nTime) {
                throw new Xinc_Exception_ModificationSet(
                    'GIT log: Cannot date in parse log line'
                );
            }
            return $nTime;
            break;
        default:
            return $strLogEntry;
            break;
        }
    }
}