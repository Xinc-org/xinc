<?php
/**
 * Xinc - Continuous Integration.
 * Repository to manage all data (html templates etc)
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Data
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

require_once 'PEAR/Config.php';

class Xinc_Data_Repository
{

    private static $_instance;
    
    private $_defaultEngine;

    private $_engines = array();
    
    private $_baseDir;
    
    /**
     * Constructor for the data repository
     */
    private function __construct()
    {
        $pearDataDir = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc';
        $customInstallDataDir = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'data';
        if (is_dir($pearDataDir)) {
            $this->_baseDir = $pearDataDir;
        } else if (is_dir($customInstallDataDir)) {
            $this->_baseDir = $customInstallDataDir;
        }
    }

    /**
     * Get an instance of the Data Repository
     *
     * @return Xinc_Data_Repository
     */
    public static function getInstance()
    {
        if (!Xinc_Data_Repository::$_instance) {
            Xinc_Data_Repository::$_instance = new Xinc_Data_Repository();
        }
        return Xinc_Data_Repository::$_instance;
    }

    /**
     * Calculates the absolute pathname of a file
     *
     * @param string $fileName
     *
     * @return string
     */
    public function get($fileName)
    {
        return $this->_baseDir . DIRECTORY_SEPARATOR . $fileName;
    }
}