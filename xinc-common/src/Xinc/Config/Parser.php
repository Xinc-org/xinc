<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Parser for the xinc system.xml file
 * Reads the system configuration of xinc
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Config
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

require_once 'Xinc/Config/Element/Iterator.php';

class Xinc_Config_Parser
{
    /**
     * @var Xinc_Config_File
     */
    private $_configFile;
    
    /**
     *
     * @param Xinc_Config_File $configFile
     */
    public function __construct(Xinc_Config_File $configFile)
    {
        $this->_configFile = $configFile;
    }

    /**
     * Returns all configured plugin entries
     *
     * @return Xinc_Config_Element_Iterator
     */
    public function getPlugins()
    {
        $plugins = $this->_configFile->xpath("//plugin");
        return new Xinc_Config_Element_Iterator($plugins);
    }

    /**
     * Returns all configured engine entries
     *
     * @return Xinc_Config_Element_Iterator
     */
    public function getEngines()
    {
        $engines = $this->_configFile->xpath("//engine");
        return new Xinc_Config_Element_Iterator($engines);
    }    

    /**
     * Returns all config setting entries
     *
     * @return Xinc_Config_Element_Iterator
     */
    public function getConfigSettings()
    {
        $settings = $this->_configFile->xpath("//setting");
        return new Xinc_Config_Element_Iterator($settings);
    }
}