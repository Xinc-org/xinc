<?php
/**
 * Xinc - Continuous Integration.
 * This interface represents a publishing mechanism to publish build results
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Publisher
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

require_once 'Xinc/Plugin/Repos/Publisher/Deliverable/Task.php';

class Xinc_Plugin_Repos_Publisher_Documentation_Task extends Xinc_Plugin_Repos_Publisher_Deliverable_Task
{
    private $index = null;

    public function getName()
    {
        return 'documentation';
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function validateTask()
    {
        if (null === $this->fileName || null === $this->index || null === $this->alias) {
            Xinc_Logger::getInstance()->error('File, Index and Alias must be specified for documentation publisher.');
            return false;
        }
        return true;
    }

    public function publish(Xinc_Build_Interface $build)
    {
        return $this->plugin->registerDocumentation($build, $this->fileName, $this->alias, $this->index);
    }
}
