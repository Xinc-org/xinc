<?php
/**
 * Xinc - Continuous Integration.
 * Interface for a Xinc Api Module that handles API calls
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Api
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2013 Alexander Opitz, Leipzig
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

require_once 'Xinc/Plugin/Interface.php';
require_once 'Xinc/Api/Module/Interface.php';

abstract class Xinc_Api_Module_Abstract implements Xinc_Api_Module_Interface
{
    /**
     * The bundled plugin
     *
     * @var Xinc_Plugin_Interface
     */
    protected $plugin;

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
    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->plugin = $plugin;
    }
}
