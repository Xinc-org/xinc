<?php
/**
 * PUT DESCRIPTION HERE
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
 * @link       http://dev.zend.com/package/PackageName
 * @author     Arno Schneider
 * @since      Class available since Release 1.2.0
 * @deprecated Class deprecated in Release 2.0.0
 */
require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Gui/Homepage/Widget.php';
class Xinc_Plugin_Repos_Gui_Homepage  extends Xinc_Plugin_Base
{
    public function validate()
    {
        return true;
    }
    public function getGuiWidgets()
    {
        return array(new Xinc_Plugin_Repos_Gui_Homepage_Widget($this));
    }
}
