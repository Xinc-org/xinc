<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Publisher
 * @subpackage Checkstyle
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2013 Alexander Opitz, Leipzig
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

require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Publisher_Checkstyle_Task extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
    /**
     * @var string Name of the task
     */
    protected $name = 'checkstyle';

    /**
     * @var string The xml file to get data from.
     */
    private $file = null;

    /**
     * Sets the checkstyle xml filename string
     *
     * @param string $file The checkstyle string filename string.
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validateTask()
    {
        if (!isset($this->file)) {
            return false;
        }

        return true;
    }

    public function publish(Xinc_Build_Interface $build)
    {
        return $this->plugin->generateStats($build, $this->file);
    }
}
