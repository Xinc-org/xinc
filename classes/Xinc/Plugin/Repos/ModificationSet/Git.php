<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet
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

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Git/Task.php';

class Xinc_Plugin_Repos_ModificationSet_Git
    extends Xinc_Plugin_Base
{
    /**
     * @var string Path to project
     */
    private $strPath;

    /**
     * @var VersionControl_Git The git object.
     */
    private $git = null;

    public function __construct()
    {
        try {
            $this->strPath = Xinc_Ini::getInstance()->get('path', 'git');
        } catch (Exception $e) {
            $this->strPath = 'git';
        }
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_ModificationSet_Git_Task($this));
    }


    /**
     * Check if this modification set has been modified
     *
     * @param Xinc_Build_Interface                       $build The running build.
     * @param Xinc_Plugin_Repos_ModificationSet_Git_Task $task The configured task
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result The result of the check.
     */
    public function checkModified(
        Xinc_Build_Interface $build,
        Xinc_Plugin_Repos_ModificationSet_Git_Task $task
    ) {
        $result = new Xinc_Plugin_Repos_ModificationSet_Result();

        try {
            $this->git = new VersionControl_Git($task->getDirectory());
            $strBranch = $this->git->getCurrentBranch();

            $strRemoteHash = $this->getRemoteHash($strBranch);
            $strLocalHash = $this->getLocalHash($strBranch);
        } catch(Exception $e) {
            $build->error('Test of GIT Repos failed: ' . $e->getMessage());
            $result->setStatus(
                Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED
            );
            return $result;
        }

        $result->setRemoteRevision($strRemoteHash);
        $result->setLocalRevision($strLocalHash);

var_dump($strRemoteHash);
var_dump($strLocalHash);

        if ($strRemoteHash !== $strLocalHash) {
            $this->fetch();
            $this->getModifiedFiles($res);
            $this->getChangeLog($res);

            if ($task->getUpdate()) {
                try {
                    $this->update();
                } catch(Exception $e) {
                    $build->error('Update of GIT local failed: ' . $e->getMessage());
                    $result>setStatus(
                        Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED
                    );
                    return $res;
                }
            }
            $result->setStatus(
                Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED
            );
        }

        return $result;
    }


    /**
     * Update local copy of git.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
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


    /**
     * Fetches remote branch state.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
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


    /**
     * Gets remote hash for branch.
     *
     * @param string $strBranchName Name of branch to get hash.
     *
     * @return string The remote hash for given branch.
     * @throw Xinc_Exception_ModificationSet
     */
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


    /**
     * Gets local hash for branch.
     *
     * @param string $strBranchName Name of branch to get hash.
     *
     * @return string The local hash for given branch.
     * @throw Xinc_Exception_ModificationSet
     */
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


    /**
     * Gets the modified files between two revisions from git and puts this info
     * into the ModificationSet_Result.
     *
     * @param Xinc_Plugin_Repos_ModificationSet_Result $result The Result to get
     *  Hash ids from and set modified files.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
    protected function getModifiedFiles(
        Xinc_Plugin_Repos_ModificationSet_Result $result
    ) {
        $command = $this->git->getCommand('diff')
            ->setOption('name-status')
            ->addArgument(
                $result->getLocalRevision() . '..' . $result->getRemoteRevision()
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
                $result->addUpdatedResource($strFile, $strAuthor);
                break;
            case 'D': //Deleted
                $result->addDeletedResource($strFile, $strAuthor);
                break;
            case 'A': //Added
            case 'C': //Copied
                $result->addNewResource($strFile, $strAuthor);
                break;
            case 'U': // Unmerged
            case 'X': // Unknown
            case 'B': // Broken pairing
            default:
                $result->addConflictResource($strFile, $strAuthor);
                break;
            }
        }
    }

    /**
     * Gets the changelog data between two revisions from git and puts this info
     * into the ModificationSet_Result. (This are author, date and commit message.)
     *
     * @param Xinc_Plugin_Repos_ModificationSet_Result $result The Result to get
     *  Hash ids from and set change log data.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
    protected function getChangeLog(
        Xinc_Plugin_Repos_ModificationSet_Result $result
    ) {
        $command = $this->git->getCommand('log')
            ->setOption('pretty', 'H:%H%nA:%aN%nD:%aD%nM:%s')
            ->addArgument(
                $result->getLocalRevision() . '..' . $result->getRemoteRevision()
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

            $result->addLogMessage($strHash, $strDate, $strAuthor, $strMessage);
        }
    }

    /**
     * Analiezes the log entry data from the git command line call.
     *
     * @param string $strType       Type to handle next.
     * @param array  &$arLogEntries The entries from the command line call.
     *  Processed ones will be shifted out.
     *
     * @return string|integer The value of the log entry.
     * @throw Xinc_Exception_ModificationSet
     */
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

    /**
     * Validate if the plugin can run properly on this system
     *
     * @return boolean True if plugin can run properly otherwise false.
     */
    public function validate()
    {
        if (!@include_once 'VersionControl/Git.php') {
            Xinc_Logger::getInstance()->error(
                'PEAR:VersionControl_Git not installed.'
            );
            return false;
        }

        return true;
    }
}