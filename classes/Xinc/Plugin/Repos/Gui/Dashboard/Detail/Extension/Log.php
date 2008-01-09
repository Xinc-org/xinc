<?php
/**
 * LogDetail Widget Extension, registers the logdetails view in the project details tab
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

require_once 'Xinc/Gui/Widget/Extension/Interface.php';
require_once 'Xinc/Data/Repository.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension_Log extends Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{

    public function getTitle()
    {
        return 'Log Message';
    }
    
    public function getContent(Xinc_Build_Interface &$build)
    {

        /**$statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        
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
        $i = count($logXml->children());
       
        $rows = array();
        foreach ($logXml->children() as $logEntry) { 
           
            $rows[] = '[' . $i-- . ',' 
                     . $logEntry['timestamp'] 
                     . ',"' . $logEntry['priority'] 
                     . '","' . str_replace("\n", '\\n', addcslashes($logEntry, '"\'')) 
                     . '"]';
        }*/
        
        $logTemplateFile = Xinc_Data_Repository::getInstance()->get('templates'
                                                                   . DIRECTORY_SEPARATOR
                                                                   . 'dashboard'
                                                                   . DIRECTORY_SEPARATOR
                                                                   . 'detail'
                                                                   . DIRECTORY_SEPARATOR
                                                                   . 'extension'
                                                                   . DIRECTORY_SEPARATOR
                                                                   . 'logJs.phtml');
        $logTemplate = file_get_contents($logTemplateFile);
        
        $content = str_replace(array('{projectname}','{buildtime}'),
                               array($build->getProject()->getName(), $build->getBuildTime()),
                               $logTemplate);
        
        return $content;
    }
    
    public function getExtensionPoint()
    {
        return 'BUILD_DETAILS';
    }
}