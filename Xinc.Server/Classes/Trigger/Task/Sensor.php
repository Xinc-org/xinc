<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Plugin
 * @subpackage Trigger
 * @author     Arno Schneider <username@example.org>
 * @copyright  2007 Arno Schneider, Barcelona
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

require_once 'Xinc/Trigger/Task/AbstractTask.php';

class Xinc_Trigger_Task_Sensor extends Xinc_Trigger_Task_AbstractTask
{
    /**
     * @var integer Task Slot INIT_PROCESS
     */
    protected $pluginSlot = Xinc_Plugin_Slot::INIT_PROCESS;

    /**
     * @var string Name of the task
     */
    protected $name = 'sensor';

    /**
     * File to test for existence.
     *
     * @var string
     */
    private $file = null;

    /**
     * Value to test inside the file.
     *
     * @var string
     */
    private $filevalue = null;

    /**
     * Sets the sensor filename string
     *
     * @param string $file The sensor filename string.
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Gets the sensor filename string
     *
     * @return string The sensor filename string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate()
    {
        return ($this->file !== null);
    }

    /**
     * Calculates the next build timestamp.
     *
     * @param Xinc_Build_Interface $build
     *
     * @return integer next build timestamp
     */
    public function getNextTime(Xinc_Build_Interface $build)
    {
        if (file_exists($this->file)) {
            unlink($this->file);
            return time();
        }
        return null;
    }
}
