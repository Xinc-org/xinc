<?php
/**
 * Xinc - Continuous Integration.
 * Parses the plugin definitions
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin
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

require_once 'Xinc/Plugin/Repository.php';
require_once 'Xinc/Plugin/Exception/FileNotFound.php';
require_once 'Xinc/Plugin/Exception/Invalid.php';
require_once 'Xinc/Plugin/Exception/ClassNotFound.php';

class Xinc_Plugin_Parser
{
    /**
     * Public parse function
     * 
     * @param  Xinc_Config_Element_Iterator $iterator
     *
     * @throws Xinc_Plugin_Task_Exception
     * @throws Xinc_Plugin_Exception_FileNotFound
     * @throws Xinc_Plugin_Exception_Invalid
     * @throws Xinc_Plugin_Exception_ClassNotFound
     */
    public static function parse(Xinc_Config_Element_Iterator $iterator)
    {
        while ($iterator->hasNext()) {
            try {
                self::loadPlugin($iterator->next());
            } catch (Exception $e) {
                Xinc_Logger::getInstance()->error('Plugins: ' . $e->getMessage());
            }
        }
    }

    /**
     * Enter description here...
     *
     * @param SimpleXMLElement $pluginXml
     *
     * @throws Xinc_Plugin_Exception_FileNotFound
     * @throws Xinc_Plugin_Exception_ClassNotFound
     * @throws Xinc_Plugin_Exception_Invalid
     * @throws Xinc_Plugin_Task_Exception
     */
    private static function loadPlugin(SimpleXMLElement $pluginXml)
    {
        $plugins = array();

        $attributes = $pluginXml->attributes();

        Xinc_Logger::getInstance()->info(
            'Registering plugin: ' . $attributes->classname . ' from file ' . $attributes->filename
        );

        $res = include_once((string) $attributes->filename);

        if (!$res && !class_exists((string) $attributes->classname)) {
            throw new Xinc_Plugin_Exception_FileNotFound(
                (string) $attributes->classname,
                (string) $attributes->filename
            );
        }
        if (!class_exists((string) $attributes->classname)) {
            throw new Xinc_Plugin_Exception_ClassNotFound(
                (string) $attributes->classname,
                (string) $attributes->filename
            );
        }

        $classname = (string) $attributes->classname;

        $plugin = new $classname;

        if (!$plugin instanceof Xinc_Plugin_Interface) {
            throw new Xinc_Plugin_Exception_Invalid((string) $attributes->classname);
        }

        Xinc_Plugin_Repository::getInstance()->registerPlugin($plugin);
    }
}
