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
        $result = $this->json_encode($responseObject->get());
        return $result;
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