<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Interface for a Xinc Api Module that handles API calls
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Api
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

require_once 'Xinc/Api/Response/Object.php';

interface Xinc_Api_Module_Interface
{
    /**
     * Constructor for the Module
     * 
     * The plugin passed itself as a variable
     * to the constructor.
     * 
     * The Api Module can access the plugins shared functionality
     *
     * @param Xinc_Plugin_Interface $plugin
     */
    public function __construct(Xinc_Plugin_Interface &$plugin);
    
    /**
     * The Xinc_Api_Handler passes the called methodName and
     * the parameters to the api module
     *
     * @param String $methodName
     * @param array $params
     */
    public function processCall($methodName, $params = array());
    
    /**
     * returns the Methods of this Api Module
     * 
     * getName() returns 'mymodule';
     * getMethod() returns array('get', 'set');
     * Api can handle: 
     *   http://mydomain.com/api/mymodule/get/
     *     AND
     *   http://mydomain.com/api/mymodule/set/
     *
     */
    public function getMethods();
    
    
    
    /**
     * get the name of the module as it should be used in api calls
     * 
     * @return String
     */
    public function getName();

}
