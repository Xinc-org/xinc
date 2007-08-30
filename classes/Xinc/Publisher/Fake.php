<?php
/**
 * This is an example publisher, 
 * 
 * @package Xinc.Publisher
 * @author David Ellis
 * @author Gavin Foster
 * @version 1.0
 * @copyright 2007 David Ellis, One Degree Square
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *	This file is part of Xinc.
 *	Xinc is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU Lesser General Public License as published by
 *	the Free Software Foundation; either version 2.1 of the License, or
 *	(at your option) any later version.
 *
 *	Xinc is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Lesser General Public License for more details.
 *
 *	You should have received a copy of the GNU Lesser General Public License
 *	along with Xinc, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Publisher/Interface.php';

class Xinc_Publisher_Fake implements Xinc_Publisher_Interface
{
    /**
     * Whether this publisher has been called
     *
     * @var boolean
     */
	private $hasPublished = false;

	/**
	 * Return whether this published has been called
	 *
	 * @return boolean
	 */
	public function getHasPublished() 
	{
		return $this->hasPublished;
	}

	/**
	 * Call this publisher
	 *
	 */
	public function publish() 
	{
		$this->hasPublished = true;
	}
	
	/**
	 * Returns whether the publisher wants to publish based on the build result.
	 *
	 * @param boolean $buildStatus
	 * @return boolean
	 */
	public function publishOn($buildStatus) 
	{
		return $buildStatus;
	}

	/**
	 * Check necessary variables are set
	 *
	 */
	public function validate()
	{
	}
}