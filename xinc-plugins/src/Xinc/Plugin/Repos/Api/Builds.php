<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Api
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

require_once 'Xinc/Api/Module/Interface.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';

class Xinc_Plugin_Repos_Api_Builds implements Xinc_Api_Module_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Plugin_Interface
     */
    protected $_plugin;

    /**
     *
     * @param Xinc_Plugin_Interface $plugin
     */
    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'builds';
    }

    /**
     *
     * @return array
     */
    public function getMethods()
    {
        return array('get');
    }

    /**
     *
     * @param string $methodName
     * @param array $params
     *
     * @return Xinc_Api_Response_Object
     */
    public function processCall($methodName, $params = array())
    {
        switch ($methodName){
            case 'get':
                return $this->_getBuilds($params);
                break;
        }
    }

    /**
     * get builds and return them
     *
     * @param array $params
     *
     * @return Xinc_Api_Response_Object
     */
    private function _getBuilds($params)
    {
        $project = isset($params['p']) ? $params['p'] : null;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : null;
        $builds = $this->_getHistoryBuilds($project, $start, $limit);
        $responseObject = new Xinc_Api_Response_Object();
        $responseObject->set($builds);

        return $responseObject;
    }

    /**
     * Get a list of all builds of a project
     *
     * @param string $projectName
     * @param integer $start
     * @param integer $limit
     *
     * @return stdClass
     */
    private function _getHistoryBuildsOld($projectName, $start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        $project = new Xinc_Project();
        $project->setName($projectName);
        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $totalCount = count($buildHistoryArr);
        if ($limit==null) {
            $limit = $totalCount;
        }
        /**
         * turn it upside down so the latest builds appear first
         */
        $buildHistoryArr = array_reverse($buildHistoryArr, true);
        $buildHistoryArr = array_slice($buildHistoryArr, $start, $limit, true);

        $builds = array();

        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize(
                    $project,
                    $buildTimestamp,
                    Xinc_Gui_Handler::getInstance()->getStatusDir()
                );
                $timezone = $buildObject->getConfigDirective('timezone');
                if ($timezone !== null) {
                    Xinc_Timezone::set($timezone);
                }
                $builds[] = array(
                    'buildtime'    => date(
                        'Y-m-d H:i:s',
                        $buildObject->getBuildTime()
                    ),
                    'timezone'     => Xinc_Timezone::get(),
                    'buildtimeRaw' => $buildObject->getBuildTime(),
                    'label'        => $buildObject->getLabel(),
                    'status'       => $buildObject->getStatus()
                );
                /**
                 * restore to system timezone
                 */
                $xincTimezone = Xinc_Gui_Handler::getInstance()->getConfigDirective('timezone');
                if ($xincTimezone !== null) {
                    Xinc_Timezone::set($xincTimezone);
                } else {
                    Xinc_Timezone::reset();
                }
            } catch (Exception $e) {
                // TODO: Handle
            }
        }

        //$builds = array_reverse($builds);

        $object = new stdClass();
        $object->totalcount = $totalCount;
        $object->builds = $builds;
        //return new Xinc_Build_Iterator($builds);
        return $object;
    }

    private function _getHistoryBuilds($projectName, $start, $limit=null)
    {
        $project = new Xinc_Project();
        $project->setName($projectName);
        try {
            $buildHistoryArr = Xinc_Build_History::getFromTo($project, $start, $limit);
            $totalCount = Xinc_Build_History::getCount($project);
            $builds = array();

            foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
                try {
                    //echo $buildTimestamp . ' - '. $buildFileName . "<br>";
                    //Xinc_Build_Repository::getBuild($project, $buildTimestamp);
                    //$buildObject = Xinc_Build::unserialize($project,
                    //                                       $buildTimestamp,
                    //                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                    $buildObject = Xinc_Build_Repository::getBuild($project, $buildTimestamp);
                    $timezone = $buildObject->getConfigDirective('timezone');
                    if ($timezone !== null) {
                        Xinc_Timezone::set($timezone);
                    }
                    $builds[] = array(
                        'buildtime'    => date(
                            'Y-m-d H:i:s', $buildObject->getBuildTime()
                        ),
                        'timezone'     => Xinc_Timezone::get(),
                        'buildtimeRaw' => $buildObject->getBuildTime(),
                        'label'        => $buildObject->getLabel(),
                        'status'       => $buildObject->getStatus()
                    );
                    /**
                    * restore to system timezone
                    */
                    $xincTimezone = Xinc_Gui_Handler::getInstance()->getConfigDirective('timezone');
                    if ($xincTimezone !== null) {
                        Xinc_Timezone::set($xincTimezone);
                    } else {
                        Xinc_Timezone::reset();
                    }
                } catch (Exception $e) {
                    // TODO: Handle
                }
            }
        //$builds = array_reverse($builds);
        } catch (Exception $e) {
            $builds = array();
            $totalCount = 0;
        }
        $object = new stdClass();
        $object->totalcount = $totalCount;
        $object->builds = $builds;
        //return new Xinc_Build_Iterator($builds);
        return $object;
    }
}