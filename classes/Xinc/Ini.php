<?php
/**
 * Holds configuration directives for xinc and possible plugins
 * 
 * @package Xinc
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

class Xinc_Ini
{
    private static $_instance;
    private $_fileName;
    private $_ini;
    
    public static function getInstance()
    {
        if (isset(self::$_instance)) {
            return self::$_instance;
        } else {
            self::$_instance = new Xinc_Ini();
        }
        return self::$_instance;
    }
    
    private function __construct()
    {
        
        include_once 'PEAR/Config.php';
        if (!class_exists('PEAR_Config')) {
            return false;
        }
        $pearDir = PEAR_Config::singleton()->get('php_dir');
        $this->_fileName = $pearDir . DIRECTORY_SEPARATOR . 'xinc.ini';
        if (file_exists($this->_fileName)) {
            $this->_ini = @parse_ini_file($this->_fileName, true);
            if (!is_array($this->_ini)) {
                $this->_ini = array();
            }
        } else {
            $this->_ini = array();
        }
    }
    
    public function get($name, $section = null)
    {
        if ($section == null) {
            return isset($this->_ini[$name]) ? $this->_ini[$name]:null;
        } else if (isset($this->_ini[$section])) {
            return isset($this->_ini[$section][$name]) ? $this->_ini[$section][$name]:null;
        } else {
            return null;
        }
    }
    
    public function set($name, $value, $section = null)
    {
        if ($section == null) {
            $this->_ini[$name] = $value;
        } else if (is_array($this->_ini[$section])){
             $this->_ini[$section][$name] = $value;
        } else {
            $this->_ini[$section] = array($name => $value);
        }
    }
    
    public function save()
    {
        return $this->_write($this->_fileName, $this->_ini);
    }
    
    private function _write($path, $assoc_arr) {
        $content = "";

        foreach ($assoc_arr as $key=>$elem) {
            if (is_array($elem)) {
                if ($key != '') {
                    $content .= "[".$key."]\r\n";                   
                }
               
                foreach ($elem as $key2=>$elem2) {
                    if ($this->_beginsWith($key2,'Comment_') == 1 && $this->_beginsWith($elem2,';')) {
                        $content .= $elem2."\r\n";
                    }
                    else if ($this->_beginsWith($key2,'Newline_') == 1 && ($elem2 == '')) {
                        $content .= $elem2."\r\n";
                    }
                    else {
                        $content .= $key2." = ".$elem2."\r\n";
                    }
                }
            }
            else {
                $content .= $key." = ".$elem."\r\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return false;
        }
        if (!fwrite($handle, $content)) {
            return false;
        }
        fclose($handle);
        return true;
    }

    private function _beginsWith( $str, $sub ) {
        return ( substr( $str, 0, strlen( $sub ) ) === $sub );
    }
}