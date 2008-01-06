<?php
/**
 * Api to get log messages for a build
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Api/Module/Interface.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';

class Xinc_Plugin_Repos_Api_LogMessages implements Xinc_Api_Module_Interface
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
   
   
    /**
     * Get a list of all builds of a project
     *
     * @param string $projectName
     * @param integer $start
     * @param integer $limit
     * @return stdClass
     */
    private function _getLogMessagesArr($projectName, $buildTime, $start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        $project = new Xinc_Project();
        $project->setName($projectName);
        $build = Xinc_Build::unserialize($project, $buildTime, $statusDir);
        
        $detailDir = $statusDir.DIRECTORY_SEPARATOR .$build->getProject()->getName();
        $year = date('Y', $build->getBuildTime());
        $month = date('m', $build->getBuildTime());
        $day = date('d', $build->getBuildTime());
        $detailDir .= DIRECTORY_SEPARATOR .
                      $year . $month . $day . 
                      DIRECTORY_SEPARATOR . 
                      $build->getBuildTime();
      
        $logXmlFile = $detailDir.DIRECTORY_SEPARATOR.'buildlog.xml';
                        
        if (file_exists($logXmlFile)) {
            $logXml = new SimpleXMLElement(file_get_contents($logXmlFile));
            
        } else {
            $logXml = new SimpleXmlElement('<log/>');
        }
        $totalCount = count($logXml->children());
        $i = $totalCount;
        $logmessages = array();
        foreach ($logXml->children() as $logEntry) { 
           
            
            $attributes = $logEntry->attributes();
            $logmessages[] = array( 'id'=>$i--, 
                     'date'=> (string)$attributes->timestamp,'priority'=>(string)$attributes->priority,'message'=>str_replace("\n", '\\n', addcslashes($logEntry, '"\'')));
        }
        $logmessages = array_slice($logmessages, $start, $limit, false);

        $object = new stdClass();
        $object->totalmessages = $totalCount;
        $object->logmessages = $logmessages;
        //return new Xinc_Build_Iterator($builds);
        return $object;
    }
}