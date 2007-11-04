<?php
/**
 * Interface
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

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @copyright  2005 Zend Technologies
 * @license    http://www.zend.com/license/3_0.txt   PHP License 3.0
 * @version    Release: @package_version@
 * @author     Arno Schneider
 * @since      Class available since Release 2.0.0
 */
interface Xinc_Project_Build_Status_Interface
{
    
    const INITIAL=-2;
    const FAILED=0;
    const PASSED=1;
    const STOPPED=-1;
    
    public function setProject(Xinc_Project &$project);
    
    public function serialize();
    
    public function unserialize();
    
    public function setProperty($name,$value);
    
    public function getProperty($name);
    
    public function setStatus($status);
    
    public function getStatus();
    
    public function setBuildTime($timestamp);
    
    public function getBuildTime();
    
    public function getLastBuildStatus();
    
    public function addBuildLabel($labelg);
    
    public function getBuildLabels();
    
    public function buildSuccessful();
    public function buildFailed();
}