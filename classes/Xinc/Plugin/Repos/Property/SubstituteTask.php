<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Property setter task
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Property
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

require_once 'Xinc/Plugin/Task/Base.php';
require_once 'Xinc/Plugin/Task/Setter/Interface.php';

class Xinc_Plugin_Repos_Property_SubstituteTask
    extends Xinc_Plugin_Task_Base
    implements Xinc_Plugin_Task_Setter_Interface
{
    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate()
    {
        return true;
    }

    /**
     * Returns name of Task.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return 'propertySubstitution';
    }

    /**
     * Returns the slot of this task inside a build.
     *
     * @return integer The slot number.
     */
    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::PROJECT_SET_VALUES;
    }

    public function process(Xinc_Build_Interface $build)
    {
        $build->debug('Setting property "${' . $this->_name . '}" to "' . $this->_value . '"');
        //$build->getProperties()->set($this->_name, $this->_value);
    }

    public function set(Xinc_Build_Interface $build, $value)
    {
        $newvalue = $build->getProperties()->parseString($value);
        return $newvalue;
    }
}