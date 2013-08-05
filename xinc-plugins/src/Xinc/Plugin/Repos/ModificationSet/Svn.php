<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet
 * @author    Arno Schneider <username@example.org>
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
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

require_once 'Xinc/Exception/ModificationSet.php';
require_once 'Xinc/Ini.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/Plugin/Abstract.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Result.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Svn/Task.php';

class Xinc_Plugin_Repos_ModificationSet_Svn extends Xinc_Plugin_Abstract
{
    /**
     * @var string Path to project.
     */
    private $strPath;

    /**
     * @var VersionControl_SVN The svn object.
     */
    private $svn = null;

    /**
     * @var Xinc_Plugin_Repos_ModificationSet_Svn_Task The task config.
     */
    private $task = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $this->strPath = Xinc_Ini::getInstance()->get('path', 'svn');
        } catch (Exception $e) {
            $this->strPath = 'svn';
        }
    }

    /**
     * Returns definition of task.
     *
     * @return array Array of definition.
     */
    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_ModificationSet_Svn_Task($this));
    }


    /**
     * Checks whether the Subversion project has been modified.
     *
     * @param Xinc_Build_Interface                       $build The running build.
     * @param Xinc_Plugin_Repos_ModificationSet_Svn_Task $task  The configured task
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result The result of the check.
     */
    public function checkModified(
        Xinc_Build_Interface $build,
        Xinc_Plugin_Repos_ModificationSet_Svn_Task $task
    ) {
        $result = new Xinc_Plugin_Repos_ModificationSet_Result();

        try {
            $this->task = $task;
            $this->svn = VersionControl_SVN::factory(
                array('info', 'log', 'status', 'update'), 
                array(
                    'fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_ASSOC,
                    // @TODO VersionControl_SVN doesn't work as documented.
                    // 'path'      => $task->getDirectory(),
                    // 'url'       => $task->getRepository(),
                    'username'  => $task->getUsername(),
                    'password'  => $task->getPassword(),
                )
            );

            $strRemoteVersion = $this->getRemoteVersion();
            $strLocalVersion = $this->getLocalVersion();
        } catch(VersionControl_SVN_Exception $e) {
            $build->error('Test of Subversion failed: ' . $e->getMessage());
            $build->setStatus(Xinc_Build_Interface::FAILED);
            $result->setStatus(
                Xinc_Plugin_Repos_ModificationSet_AbstractTask::ERROR
            );
            return $result;
        }

        $result->setRemoteRevision($strRemoteVersion);
        $result->setLocalRevision($strLocalVersion);

        if ($strRemoteVersion !== $strLocalVersion) {
            try {
                $this->getModifiedFiles($result);
                $this->getChangeLog($result);
                if ($this->task->getUpdate()) {
                    $this->update($result);
                }
                $result->setStatus(
                    Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED
                );
            } catch(Exception $e) {
                var_dump($e->getMessage());
                $build->error('Processing SVN failed: ' . $e->getMessage());
                $result->setStatus(
                    Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED
                );
            }
        }

        return $result;
    }

    /**
     * Gets remote version.
     *
     * @return string The remote version.
     */
    protected function getRemoteVersion()
    {
        return $this->getRevisionFromXML(
            $this->svn->info->run(
                array($this->task->getRepository())
            )
        );
    }

    /**
     * Gets local version.
     *
     * @return string The local version.
     */
    protected function getLocalVersion()
    {
        return $this->getRevisionFromXML(
            $this->svn->info->run(
                array($this->task->getDirectory())
            )
        );
    }

    /**
     * Returns the revison number from the PEAR::SVN Info XML
     *
     * @param array $arXml The XML as array from SVN info
     *
     * @return string Revision number
     */
    protected function getRevisionFromXML(array $arXml)
    {
        if (isset($arXml['entry'][0]['commit']['revision'])) {
            // Latest commit in this directory path
            return $arXml['entry'][0]['commit']['revision'];
        }
        // Latest commit in the whole repository
        return $arXml['entry'][0]['revision'];
    }

    /**
     * Gets the modified files between two revisions from SVN and puts this info
     * into the ModificationSet_Result.
     *
     * @param Xinc_Plugin_Repos_ModificationSet_Result $result The Result to get
     *  Hash ids from and set modified files.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
    protected function getChangeLog(
        Xinc_Plugin_Repos_ModificationSet_Result $result
    ) {
        $arLog = $this->svn->log->run(
            array($this->task->getDirectory()),
            array(
                'r' => $result->getLocalRevision() + 1
                    . ':' . $result->getRemoteRevision()
            )
        );
        if (isset($arLog['logentry'])) {
            foreach ($arLog['logentry'] as $arEntry) {
                $result->addLogMessage(
                    $arEntry['revision'],
                    strtotime($arEntry['date']),
                    $arEntry['author'],
                    $arEntry['msg']
                );
            }
        } else {
            throw new Xinc_Exception_ModificationSet(
                'SVN get log failed',
                0
            );
        }
    }


    /**
     * Gets the modified files between two revisions from svn and puts this info
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
        $arStatus = $this->svn->status->run(
            array($this->task->getDirectory()),
            array('u' => true)
        );
        $arTarget = $arStatus['target'][0];

        $result->setBasePath($arTarget['path']);

        if (isset($arTarget['entry'])) {
            foreach ($arTarget['entry'] as $entry) {
                $strFileName = $entry['path'];
                $author = null;
                if (isset($entry['repos-status'])) {
                    $strReposStatus = $entry['repos-status']['item'];
                } else {
                    $strReposStatus = '';
                }
                switch ($strReposStatus) {
                case 'modified':
                    $result->addUpdatedResource($strFileName, $author);
                    break;
                case 'deleted':
                    $result->addDeletedResource($strFileName, $author);
                    break;
                case 'added':
                    $result->addNewResource($strFileName, $author);
                    break;
                case 'conflict':
                    $result->addConflictResource($strFileName, $author);
                    break;
                }
            }
        }
    }

    /**
     * Updates local svn to the remoteRevision for this test.
     *
     * @param Xinc_Plugin_Repos_ModificationSet_Result $result The Result to get
     *  Hash ids from and set modified files.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
    protected function update(
        Xinc_Plugin_Repos_ModificationSet_Result $result
    ) {
        $arUpdate = $this->svn->update->run(
            array($this->task->getDirectory()),
            array('r' => $result->getRemoteRevision())
        );

        if (false === $arUpdate) {
            throw new Xinc_Exception_ModificationSet(
                'SVN update local working copy failed',
                0
            );
        }
    }

    /**
     * Validate if the plugin can run properly on this system
     *
     * @return boolean True if plugin can run properly otherwise false.
     */
    public function validate()
    {
        if (!@include_once 'VersionControl/SVN.php') {
            Xinc_Logger::getInstance()->error(
                'PEAR:VersionControl_SVN not installed.'
            );
            return false;
        }
        return true;
    }
}
