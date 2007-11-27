<?php
/**
 * Property setter task
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
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
require_once 'Xinc/Plugin/Task/Base.php';

class Xinc_Plugin_Repos_Property_SetTask extends Xinc_Plugin_Task_Base
{
    private $_lastPair = array();
    /**
     *
     * @var string
     */
    private $_name;
    
    /**
     *  Holding all the property value pairs
     *
     * @var array
     */
    private $_properties = array();
    /**
     *
     * @var string
     */
    private $_value;
    
    /**
     * sets the name of the property
     *
     * @param string $name
     */
    public function setName($name, $build)
    {
        $this->_name = (string)$name;
        if (isset($this->_name) && isset($this->_value)) {
            $build->getProperties()->set($this->_name, $this->_value);
        }
    }
    /**
     * sets the value of the property
     *
     * @param string $value
     */
    public function setValue($value, $build)
    {
        $this->_value = (string)$value;
        if (isset($this->_name) && isset($this->_value)) {
            $build->getProperties()->set($this->_name, $this->_value);
        }
    }
    public function validate()
    {
        foreach ( $this->_subtasks as $task ) {
            /**
             * cannot have subtasks
             */
            return false;
                
        }
        if (!isset($this->_name) && !isset($this->_value)) {
            return false;
        } else {
            return true;
        }
    }

    public function getName()
    {
        return 'property';
    }
    
    public function registerTask(Xinc_Plugin_Task_Interface &$task)
    {
        
        $this->_subtasks[]=$task;

    }
    


    public function getPluginSlot()
    {
        return Xinc_Plugin_Slot::INIT_PROCESS;
    }
    public function process(Xinc_Build_Interface &$build)
    {
        $build->debug('Setting property "${' . $this->_name . '}" to "' . $this->_value . '"');
        

        $build->getProperties()->set($this->_name, $this->_value);

        $build->setStatus(Xinc_Build_Interface::PASSED);

    }

}