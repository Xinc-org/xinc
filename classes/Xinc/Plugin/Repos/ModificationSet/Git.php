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
require_once 'Xinc/Ini.php';
/*require_once 'Xinc/Logger.php';
require_once 'Xinc/Exception/ModificationSet.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';
require_once 'Xinc/Plugin/Repos/ModificationSet/Result.php';
*/
class Xinc_Plugin_Repos_ModificationSet_Git extends Xinc_Plugin_Base
{
    private $strPath;

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

    protected function _getChangeLog(
        Xinc_Build_Interface $build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result $set,
        $fromRevision, $toRevision, $username, $password
    ) {
    }

    protected function _getModifiedFiles(
        Xinc_Build_Interface $build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result $set,
        $username, $password
    ) {
    }

    private function _update(
        Xinc_Build_Interface $build, $dir,
        Xinc_Plugin_Repos_ModificationSet_Result $set,
        $username, $password
    ) {
    }

    /**
     * Masks certain string elements with **** and returns the string
     *
     * @param string $inputStr
     * @param array $maskElements
     *
     * @return string
     */
    private function _maskOutput($inputStr, array $maskElements)
    {
        $outputStr = str_replace($maskElements, '****', $inputStr);
        return $outputStr;
    }

    /**
     * Checks whether the Subversion project has been modified.
     *
     * @return boolean
     */
    public function checkModified(Xinc_Build_Interface $build,
                                 $dir, $update = false,
                                 $username = null, $password = null)
    {
    }

    /**
     * Parse the result of an svn command for the Subversion project URL.
     *
     * @param string $result
     *
     * @return string
     * @throws Exception
     */
    private function getUrl($result)
    {
        $xml = new SimpleXMLElement($result);
        $urls = $xml->xpath('/info/entry/url');
        $url = (string) $urls[0];
        return $url;
    }

    /**
     * Parse the result of an svn command 
     * for the Subversion project revision number.
     *
     * @param string $result
     *
     * @return string
     * @throws Exception
     */
    public function getRevision($result)
    {
    }


    /**
     * Check necessary variables are set
     *
     * @throws Xinc_Exception_MalformedConfig
     */
    public function validate()
    {
        if (!@include_once 'VersionControl/Git.php') {
            throw new Xinc_Exception_MalformedConfig(
                'PEAR:VersionControl_Git not installed.'
            );
        }

        return true;
    }
}