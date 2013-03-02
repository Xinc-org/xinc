<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Api to get a listing of all projects
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

class Xinc_Plugin_Repos_Api_Projects implements Xinc_Api_Module_Interface
{
    /**
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
        return 'projects';
    }

    /**
     *
     * @return array
     */
    public function getMethods()
    {
        return array('list');
    }

    public function processCall($methodName, $params = array())
    {
        switch ($methodName){
            case 'list':
                return $this->_getProjects($params);
                break;
        }
    }

    /**
     *
     * @param array $params
     *
     * @return Xinc_Api_Response_Object
     */
    private function _getProjects($params)
    {
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : null;
        $projects = $this->_getProjectListing($start, $limit);
        $responseObject = new Xinc_Api_Response_Object();
        $responseObject->set($projects);

        return $responseObject;
    }

    /**
     *
     * @param integer $start
     * @param integer $limit
     *
     * @return array
     */
    private function _getProjectListing($start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $dh = opendir($statusDir);
        $projects = array();
        while ($file = readdir($dh)) {
            if (preg_match('/(.*)\.history/', $file, $matches)) {
                $projects[] = $matches[1];
            }
        }

        return $projects;
    }
}