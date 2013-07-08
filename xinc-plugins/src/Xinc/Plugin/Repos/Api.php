<?php
/**
 * Xinc - Continuous Integration.
 * Api Plugin - provides API methods
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos
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

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Api/Builds.php';
require_once 'Xinc/Plugin/Repos/Api/Artifacts.php';
require_once 'Xinc/Plugin/Repos/Api/Formats.php';
require_once 'Xinc/Plugin/Repos/Api/Projects.php';
require_once 'Xinc/Plugin/Repos/Api/LogMessages.php';
require_once 'Xinc/Plugin/Repos/Api/Deliverable.php';
require_once 'Xinc/Plugin/Repos/Api/Documentation.php';


class Xinc_Plugin_Repos_Api extends Xinc_Plugin_Base
{
    
    public function validate()
    {
        return true;
    }
    
    /**
     *
     * @return array of Gui Widgets
     */
    public function getApiModules()
    {
        return array(new Xinc_Plugin_Repos_Api_Builds($this),
                     new Xinc_Plugin_Repos_Api_Artifacts($this),
                     new Xinc_Plugin_Repos_Api_Deliverable($this),
                     new Xinc_Plugin_Repos_Api_Documentation($this),
                     new Xinc_Plugin_Repos_Api_Formats($this),
                     new Xinc_Plugin_Repos_Api_Projects($this),
                     new Xinc_Plugin_Repos_Api_LogMessages($this));
    }
}
