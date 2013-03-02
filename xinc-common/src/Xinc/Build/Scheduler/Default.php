<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Build-Scheduler, will only build once if not built yet
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build.Scheduler
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

require_once 'Xinc/Build/Scheduler/Interface.php';

class Xinc_Build_Scheduler_Default implements Xinc_Build_Scheduler_Interface
{

 
    private $_nextBuildTime = null;

    /**
     * Calculates the next build timestamp
     * this is a build once scheduler
     *
     * @return integer
     */
    public function getNextBuildTime(Xinc_Build_Interface $build)
    {
        if ($build->getLastBuild()->getBuildTime() == null
            && $build->getStatus() !== Xinc_Build_Interface::STOPPED
        ) {
            if (!isset($this->_nextBuildTime)) {
                $this->_nextBuildTime = time();
            }
            return $this->_nextBuildTime;
        } else {
            return null;
        }
    }
}