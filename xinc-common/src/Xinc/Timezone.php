<?php
/**
 * Xinc - Continuous Integration.
 * Helper class for timezone
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc
 * @author    Arno Schneider <username@example.com>
 * @copyright 2008 Arno Schneider, Barcelona
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

class Xinc_Timezone
{
    private static $_inited = false;
    private static $_timezone = null;
    private static $_systemTimezone = null;
    private static $_iniTimezone = null;
    
    private static function _init()
    {
        $systemTimezone = null;
        $defaultTimeZone = ini_get('date.timezone');
        self::$_iniTimezone = $defaultTimeZone;
        if (!empty($defaultTimeZone)) {
            $systemTimezone = $defaultTimeZone;
        } else if (function_exists('date_default_timezone_get')) {
            $systemTimezone = date_default_timezone_get();
        } else {
            exec('date +%Z', $output);
            $systemTimezone = $output[0];
        }
        self::$_systemTimezone = $systemTimezone;
        self::$_inited = true;
    }
    
    public static function get()
    {
        if (!self::$_inited) self::_init();
        
        if (self::$_timezone == null) {
            return self::$_systemTimezone;
        } else {
            return self::$_timezone;
        }
    }

    public static function reset()
    {
        if (!self::$_inited) self::_init();
        
        self::$_timezone = null;
        ini_set('date.timezone', self::$_systemTimezone);
        
    }

    public static function set($tz)
    {
        if (!self::$_inited) self::_init();
        if (empty($tz)) $tz = null;
        self::$_timezone = $tz;
        ini_set('date.timezone', $tz);
    }
    
    public static function getIniTimezone()
    {
        return self::$_iniTimezone;
    }
}