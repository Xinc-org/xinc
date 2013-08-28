<?php
/**
 * Xinc - Continuous Integration.
 * Api to get log messages for a build
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
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Api/Module/Abstract.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';

class Xinc_Plugin_Repos_Api_LogMessages extends Xinc_Api_Module_Abstract
{
    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'logmessages';
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
     * @return Xinc_Api_Response_Object
     */
    public function processCall($methodName, $params = array())
    {
        switch ($methodName){
            case 'get':
                return $this->_getLogMessages($params);
                break;
        }
    }

    /**
     * get logmessages and return them
     *
     * @param array $params
     *
     * @return Xinc_Api_Response_Object
     */
    private function _getLogMessages($params)
    {
        $project = isset($params['p']) ? $params['p'] : null;
        $buildtime = isset($params['buildtime']) ? $params['buildtime'] : null;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : null;
        $builds = $this->_getLogMessagesArr($project, $buildtime, $start, $limit);
        $responseObject = new Xinc_Api_Response_Object();
        $responseObject->set($builds);

        return $responseObject;
    }

    private function _getNextMessage($fh)
    {
        $message = false;
        $started = false;
        $found=false;
        while (!$found) {
            if(feof($fh)) break;
            $line = fgets($fh);
            $line = trim($line);
            if (empty($line) && !$started) continue;
            if (strstr($line, '<message ') && !$started) {
                $started = true;
                $message = '';
                //$message .= $line;
            }
            if ($started) {
                $message .= $line;
                if (strstr($line, '</message>')) {
                    $found = true;
                } else {
                    $message .= "<br/>";
                }
            }
        }
        //echo $message; echo "\n<br>";
        return $message;
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
    private function _getLogMessagesArr($projectName, $buildTime, $start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        $project = new Xinc_Project();
        $project->setName($projectName);
        $totalCount = 0;
        try {
            $build = Xinc_Build::unserialize($project, $buildTime, $statusDir);
            $timezone = $build->getConfigDirective('timezone');
            if ($timezone !== null) {
                Xinc_Timezone::set($timezone);
            }
            $detailDir = Xinc_Build_History::getBuildDir($project, $buildTime);

            $logXmlFile = $detailDir.DIRECTORY_SEPARATOR.'buildlog.xml';

            if (file_exists($logXmlFile)) {
                /**
                 * Add fopen() to the function to just get the loglines
                 * that we need.
                 * the bigger the logfiles get, the more this gets a
                 * performance problem
                 */
                $xmlStr = '';
                $pos = 0;
                $fh = fopen($logXmlFile, 'r');
                $xmlStr = fgets($fh);
                $xmlStr .= fgets($fh);
                $tagOpen = false;
                while ($pos < $start && ($message = $this->_getNextMessage($fh)) !== false) {
                    $pos++;
                    $totalCount++;
                }

                if ($limit!=null) {
                    $addClosingTag = true;
                    while ($pos<$start+$limit && ($message = $this->_getNextMessage($fh))!== false) {
                        $xmlStr.= $message;
                        $pos++;
                        $totalCount++;
                    }
                    $xmlStr .='</build>';
                } else {
                    while (($message = $this->_getNextMessage($fh))!== false) {
                        $xmlStr.= $message;
                        $totalCount++;
                        $pos++;
                    }
                    $xmlStr .='</build>';
                }
                $tagOpen = false;
                $tagClosed = false;
                while (($message = $this->_getNextMessage($fh))!== false) {
                    $totalCount++;
                    $pos++;
                }
                fclose($fh);
                $logXml = new SimpleXMLElement($xmlStr);
            } else {
                $logXml = new SimpleXmlElement('<log/>');
            }
            $totalCount = $pos; //count($logXml->children());
            $i = $pos;
            $logmessages = array();
            $id = $totalCount-$start;

            foreach ($logXml->children() as $logEntry) {
                $attributes = $logEntry->attributes();
                $logmessages[] = array(
                    'id'=>$id--,
                    'date'=> (string) $attributes->timestamp,
                    'stringdate'=> date('Y-m-d H:i:s', (int) $attributes->timestamp),
                    'timezone' => Xinc_Timezone::get(),
                    'priority' => (string) $attributes->priority,
                    'message' => base64_decode($logEntry),
                );
            }
            /**
             * restore to system timezone
             */
            $xincTimezone = Xinc_Gui_Handler::getInstance()->getConfigDirective('timezone');
            if ($xincTimezone !== null) {
                Xinc_Timezone::set($xincTimezone);
            } else {
                Xinc_Timezone::reset();
            }
            //$logmessages = array_slice($logmessages, $start, $limit, false);
        } catch (Exception $e1) {
            $totalCount = 0;
            $logmessages = array();
        }
        $object = new stdClass();
        $object->totalmessages = $totalCount;
        $object->logmessages = $logmessages;
        //return new Xinc_Build_Iterator($builds);
        return $object;
    }
}
