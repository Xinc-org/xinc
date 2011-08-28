<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * JSON Response Format
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Api.Format
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

require_once 'Xinc/Api/Response/Format/Interface.php';

class Xinc_Plugin_Repos_Api_Format_Json implements Xinc_Api_Response_Format_Interface
{
    const NAME = 'json';

    /**
     * Generates the output string that is going to be send to the calling party
     *
     * @param Xinc_Api_Response_Object $responseObject
     *
     * @return String
     */
    public function generate(Xinc_Api_Response_Object &$responseObject)
    {
        $result = $this->_json_encode($responseObject->get());
        return $result;
    }
    
    /**
     * Encodes a php object in the json format
     *
     * @param object $a
     *
     * @return string
     */
    private function _json_encode($a)
    {
        if (is_null($a)) return 'null';
        if ($a === false) return 'false';
        if ($a === true) return 'true';
        if (is_scalar($a)) {
            $a = addslashes($a);
            $a = str_replace("\n", '\n', $a);
            $a = str_replace("\r", '\r', $a);
            $a = preg_replace('{(</)(script)}i', "$1'+'$2", $a);
            return "'$a'";
        }
        $isList = true;
        for ($i=0, reset($a); $i<count($a); $i++, next($a))
            if (key($a) !== $i) {
                $isList = false; break; 
            }
        $result = array();
        if ($isList) {
            foreach ($a as $v) $result[] = $this->_json_encode($v);
            return '[ ' . join(', ', $result) . ' ]';
        } else {
            foreach ($a as $k=>$v)
                $result[] = $this->_json_encode($k) . ': ' . $this->_json_encode($v);
            return '{ ' . join(', ', $result) . ' }';
        }
    }

    /**
     * returns the name of the format
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}