<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Trigger
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2014 Alexander Opitz, Leipzig
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *             This file is part of Xinc.
 *             Xinc is free software; you can redistribute it and/or modify
 *             it under the terms of the GNU Lesser General Public License as
 *             published by the Free Software Foundation; either version 2.1 of
 *             the License, or (at your option) any later version.
 *
 *             Xinc is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Lesser General Public License for more details.
 *
 *             You should have received a copy of the GNU Lesser General Public
 *             License along with Xinc, write to the Free Software Foundation,
 *             Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link       http://code.google.com/p/xinc/
 */

\Xinc\Core\Registry\Task::getInstance()->register(
    'trigger', new \Xinc\Trigger\Task\Triggers()
);
\Xinc\Core\Registry\Task::getInstance()->register(
    'cron', new \Xinc\Trigger\Task\Cron()
);
\Xinc\Core\Registry\Task::getInstance()->register(
    'scheduler', new \Xinc\Trigger\Task\Scheduler()
);
\Xinc\Core\Registry\Task::getInstance()->register(
    'sensor', new \Xinc\Trigger\Task\Sensor()
);
