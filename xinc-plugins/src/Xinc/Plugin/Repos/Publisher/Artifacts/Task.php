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

require_once 'Xinc/Plugin/Repos/Publisher/AbstractTask.php';

class Xinc_Plugin_Repos_Publisher_Artifacts_Task extends Xinc_Plugin_Repos_Publisher_AbstractTask
{
    /**
     * Name of the artifact file.
     *
     * @var string
     */
    private $fileName;

    public function getName()
    {
        return 'artifactspublisher';
    }

    public function setFile($fileName)
    {
        $this->fileName = (string) $fileName;
    }

    public function validateTask()
    {
        if (!isset($this->fileName)) {
            Xinc_Logger::getInstance()->error('File must be specified for artifactspublisher.');
            return false;
        }
        return true;
    }

    public function publish(Xinc_Build_Interface $build)
    {
        return $this->plugin->registerArtifact($build, $this->fileName);
    }
}
