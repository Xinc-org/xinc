<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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
require_once 'Xinc/Project/Build/Labeler/Interface.php';
/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @copyright  2005 Zend Technologies
 * @license    http://www.zend.com/license/3_0.txt   PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://dev.zend.com/package/PackageName
 * @author     Arno Schneider
 * @since      Class available since Release 1.2.0
 * @deprecated Class deprecated in Release 2.0.0
 */
class Xinc_Project_Build_Labeler_Default implements Xinc_Project_Build_Labeler_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Project_Build_Status_Interface
     */
    private $_buildStatus;
    private $_buildLabel;
    private $_prefix='BUILD.';
    private $_firstBuild=1;
    private $_nextBuildNo;
    private $_previousBuildNo;
    
    
    public function setBuildStatus(Xinc_Project_Build_Status_Interface &$buildstatus)
    {
        $this->_buildStatus = $buildstatus;
    }
    
    public function getBuildLabel()
    {
        $previousBuildNo=$this->_buildStatus->getProperty('sticky.build.no');
        
        if ($previousBuildNo != null) {
            $nextBuildNo = $previousBuildNo + 1;
        } else {
            $nextBuildNo = $this->_firstBuild;
        }
        $this->_nextBuildNo=$nextBuildNo;
        $this->_previousBuildNo=$previousBuildNo;
        $buildLabel = $this->_prefix . $nextBuildNo;
        $this->_buildStatus->setProperty('build.label', $buildLabel);
        return $buildLabel;
    }
    public function buildSuccessful()
    {
        $this->_buildStatus->setProperty('sticky.build.no', $this->_nextBuildNo);
    }
    public function buildFailed()
    {
        $this->_buildStatus->setProperty('sticky.build.no', $this->_previousBuildNo);
    }
}