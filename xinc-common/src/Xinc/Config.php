<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Main configuration class, handles the system.xml.
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

require_once 'Xinc/Config/File.php';
require_once 'Xinc/Config/Parser.php';
require_once 'Xinc/Plugin/Parser.php';
require_once 'Xinc/Engine/Parser.php';

class Xinc_Config
{
    /**
     * Reads the system.xml
     * - parses it
     * - loads plugins
     * - loads engines
     *
     * @param string $fileName path to system.xml
     *
     * @return void
     * @throws Xinc_Config_Exception_FileNotFound
     * @throws Xinc_Config_Exception_InvalidEntry
     */
    public static function parse($fileName)
    {
        $configFile = Xinc_Config_File::load($fileName);
        $configParser = new Xinc_Config_Parser($configFile);

        self::_parsePlugins($configParser->getPlugins());
        self::_parseEngines($configParser->getEngines());
    }
    
    private static function _parsePlugins($plugins)
    {
        Xinc_Plugin_Parser::parse($plugins);
        $widgets = Xinc_Gui_Widget_Repository::getInstance()->getWidgets();
        
        foreach ($widgets as $path => $widget) {
            echo "Init1 on: " . get_class($widget) . "\n<br>";
            $widget->init();
        }
        
    }
    
    private static function _parseEngines($plugins)
    {
        Xinc_Engine_Parser::parse($plugins);
    }
}