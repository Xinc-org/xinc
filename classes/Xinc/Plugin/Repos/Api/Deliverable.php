<?php
declare(encoding = 'utf-8');
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
require_once 'Xinc/Build/Repository.php';
require_once 'Xinc/Api/Response/Object.php';

class Xinc_Plugin_Repos_Api_Deliverable implements Xinc_Api_Module_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Plugin_Interface
     */
    protected $_plugin;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'deliverable';
    }

    /**
     * returns methods of this api
     *
     * @return array
     */
    public function getMethods()
    {
        return array('get');
    }

    /**
     * Process an api call with the requested methodname and parameters
     *
     * @param string $methodName
     * @param array $params
     *
     * @return mixed
     */
    public function processCall($methodName, $params = array())
    {
        switch ($methodName){
            case 'get':
                return $this->_getDeliverableFile($params);
                break;
        }
    }

    /**
     * determine the mime content type of a file
     *
     * @param string $fileName
     *
     * @return string
     */
    public function mime_content_type2($fileName)
    {
        $contentType = null;
        if (preg_match('/.*?.tar\.gz/', $fileName) || 
            preg_match('/^.*?.tar$/', $fileName) ||  
            preg_match('/^.*?.tgz$/', $fileName)) {
            return 'application/x-gzip';
        }
        /**if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME); // return mime type ala mimetype extension
            if(!$finfo) return;
            $contentType = finfo_file($finfo, $fileName);
            finfo_close($finfo);
        } else*/
        if (function_exists('mime_content_type')) {
            $contentType = mime_content_type($fileName);
        }

        return $contentType;
    }

    /**
     * Load the requested artifacts file and output it to the browser
     *
     * @param array $params
     */
    private function _getDeliverableFile($params)
    {
        /**$projectName = $params['p'];
        $buildTime = $params['buildtime'];
        $file = $params['file'];*/

        $query = urldecode($_SERVER['REQUEST_URI']);

        preg_match("/\/(.*?)\/(.*?)\/(.*?)\/(.*?)\/(.*?)\/(.*?)\/(.*)/", $query, $matches);
        if (count($matches)!=8) {
            echo "Could not find deliverable";
            die();
        }
        $projectName = $matches[5];
        $buildTime = $matches[6]; // if buildtime==latest, get latest build
        // latest
        // Xinc_Build_History::get()
        $file = $matches[7];
        $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        $project = new Xinc_Project();
        $project->setName($projectName);
        try {
            /**$build = Xinc_Build::unserialize($project,
                                                 $buildTime,
                                                 Xinc_Gui_Handler::getInstance()->getStatusDir());*/
            if ($buildTime == 'latest-successful') {
                $build = Xinc_Build_Repository::getLastSuccessfulBuild($project);
                $statusDir = Xinc_Build_History::getLastSuccessfulBuildDir($project);
            } else if ($buildTime == 'latest') {
                $build = Xinc_Build_Repository::getLastBuild($project);
                $statusDir = Xinc_Build_History::getLastBuildDir($project);
            } else {
                $build = Xinc_Build_Repository::getBuild($project, $buildTime);
                $statusDir = Xinc_Build_History::getBuildDir($project, $buildTime);
            }

            $statusDir .= DIRECTORY_SEPARATOR . Xinc_Plugin_Repos_Deliverable::DELIVERABLE_DIR .
                          DIRECTORY_SEPARATOR;

            /**
             * Replace multiple / slashes with just one
             */
            $fileName = $statusDir.$file;
            $fileName = preg_replace('/\\' . DIRECTORY_SEPARATOR . '+/', DIRECTORY_SEPARATOR, $fileName);
            $realfile = realpath($fileName);
            if ($realfile != $fileName) {
                // check if we have an alias name
                $deliverables = $build->getInternalProperties()->get('deliverables');
                if (is_array($deliverables)) {
                    $aliasTest = basename($file);
                    if (isset($deliverables['aliases']) && isset($deliverables['aliases'][$aliasTest])) {
                        $aliasFile = $deliverables['deliverables'][$deliverables['aliases'][$aliasTest]];
                        if (file_exists($aliasFile) && is_file($aliasFile)) {
                            return $this->_outputDeliverable($aliasFile);
                        }
                    }
                }
                echo "Could not find artifact";
                die();
            } else if (file_exists($fileName) && is_file($realfile)) {
                return $this->_outputDeliverable($fileName);
            } else {
                echo "Could not find deliverable";
                die();
            }
        } catch (Exception $e) {
            echo "Could not find any deliverable";
            die();
        }
    }

    private function _outputDeliverable($fileName)
    {
        $responseObject = new Xinc_Api_Response_Object();
        $responseObject->set($fileName);
        return $responseObject;
    }
}