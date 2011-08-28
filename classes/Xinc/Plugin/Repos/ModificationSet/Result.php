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
 * @link      http://xincplus.sourceforge.net
 */

class Xinc_Plugin_Repos_ModificationSet_Result
{
    private $_changed = false;

    private $_previousRevision;

    private $_currentRevision;

    private $_basePath;

    private $_status;

    private $_updatedResources = array();

    private $_newResources = array();

    private $_deletedResources = array();

    private $_conflictResources = array();

    private $_mergedResources = array();

    private $_index = array();

    private $_logMessages = array();

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function setBasePath($path)
    {
        $this->_basePath = $path;
    }

    public function getBasePath()
    {
        return $this->_basePath;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function setLocalRevision($oldRevision)
    {
        $this->_previousRevision = $oldRevision;
    }

    public function getLocalRevision()
    {
        return $this->_previousRevision;
    }

    public function setRemoteRevision($newRevision)
    {
        $this->_currentRevision = $newRevision;
    }

    public function getRemoteRevision()
    {
        return $this->_currentRevision;
    }

    public function isChanged()
    {
        if ($this->_changed == true) return true;
        else return $this->_previousRevision < $this->_currentRevision;
    }

    public function setChanged($bool)
    {
        $this->_changed = $bool;
    }

    private function _getRelativeFileName($fileName)
    {
        return str_replace($this->_basePath . DIRECTORY_SEPARATOR, '', $fileName);
    }

    public function addUpdatedResource($fileName, $author)
    {
        $key = md5('U' . $fileName . $author);
        $update = array('fileName' => $this->_getRelativeFileName($fileName),
                        'author' => $author);
        if (!in_array($key, $this->_index)) {
            $this->_updatedResources[] = $update;
            $this->_index[] = $key;
        }
    }

    public function getUpdatedResources()
    {
        return $this->_updatedResources;
    }

    public function addNewResource($fileName, $author)
    {
        $key = md5('A' . $fileName . $author);
        $update = array('fileName' => $this->_getRelativeFileName($fileName),
                        'author' => $author);
        if (!in_array($key, $this->_index)) {
            $this->_newResources[] = $update;
            $this->_index[] = $key;
        }
    }

    public function getNewResources()
    {
        return $this->_newResources;
    }

    public function addDeletedResource($fileName, $author)
    {
        $key = md5('D' . $fileName . $author);
        $update = array('fileName' => $this->_getRelativeFileName($fileName),
                        'author' => $author);
        if (!in_array($key, $this->_index)) {
            $this->_deletedResources[] = $update;
            $this->_index[] = $key;
        }
    }

    public function getDeletedResources()
    {
        return $this->_deletedResources;
    }

    public function addConflictResource($fileName, $author)
    {
        $key = md5('C' . $fileName . $author);
        $update = array('fileName' => $this->_getRelativeFileName($fileName),
                        'author' => $author);
        if (!in_array($key, $this->_index)) {
            $this->_conflictResources[] = $update;
            $this->_index[] = $key;
        }
    }

    public function getConflictResources()
    {
        return $this->_conflictResources;
    }

    public function addMergedResource($fileName, $author)
    {
        $key = md5('M' . $fileName . $author);
        $update = array('fileName' => $this->_getRelativeFileName($fileName),
                        'author' => $author);
        if (!in_array($key, $this->_index)) {
            $this->_mergedResources[] = $update;
            $this->_index[] = $key;
        }
    }

    public function getMergedResources()
    {
        return $this->_mergedResources;
    }

    public function addLogMessage($revision, $date, $author, $message)
    {
        $key = md5('LM' . $revision. $date. $author. $message);
        $message = array ('revision' => $revision,
                          'date' => $date,
                          'author' => $author,
                          'message' => $message);
        if (!in_array($key, $this->_index)) {
            $this->_logMessages[] = $message;
            $this->_index[] = $key;
        }
    }

    public function getLogMessages()
    {
        return $this->_logMessages;
    }

    public function mergeResultSet(Xinc_Plugin_Repos_ModificationSet_Result $set)
    {
        foreach ($set->getConflictResources() as $res) {
            $key = md5('C' . $res['filename'] . $res['author']);
            if (!in_array($key, $this->_index)) {
                $this->_conflictResources[] = $res;
                $this->_index[] = $key;
            }
        }
        foreach ($set->getUpdatedResources() as $res) {
            $key = md5('U' . $res['filename'] . $res['author']);
            if (!in_array($key, $this->_index)) {
                $this->_updatedResources[] = $res;
                $this->_index[] = $key;
            }
        }
        foreach ($set->getNewResources() as $res) {
            $key = md5('A' . $res['filename'] . $res['author']);
            if (!in_array($key, $this->_index)) {
                $this->_newResources[] = $res;
                $this->_index[] = $key;
            }
        }
        foreach ($set->getMergedResources() as $res) {
            $key = md5('M' . $res['filename'] . $res['author']);
            if (!in_array($key, $this->_index)) {
                $this->_mergedResources[] = $res;
                $this->_index[] = $key;
            }
        }
        foreach ($set->getLogMessages() as $res) {
            $key = md5('LM' . $res['revision']. $res['date']. $res['author']. $res['message']);
            if (!in_array($key, $this->_index)) {
                $this->_logMessages[] = $res;
                $this->_index[] = $key;
            }
        }
    }

    public function __toString()
    {
        $output  = "ModificationSet\n";
        $output .= "Previous Revision: " . $this->_previousRevision . "\n";
        $output .= "New Revision: " . $this->_currentRevision . "\n";
        $output .= "Change detected: " . ($this->isChanged() ? 'yes':'no') . "\n";
        $output .= "Added Resources: " . count($this->_newResources) . "\n";
        $output .= "Modified Resources: " . count($this->_updatedResources) . "\n";
        $output .= "Deleted Resources: " . count($this->_deletedResources) . "\n";
        $output .= "Conflict Resources: " . count($this->_conflictResources) . "\n";
        $output .= "Merged Resources: " . count($this->_mergedResources) . "\n";
        return $output;
    }
}