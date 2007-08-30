<?php

/**
 *	
 * This is an example modification set, 
 * 
 * 
 * 
 * @package Xinc
 * @author  David Ellis
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

class Xinc_ModificationSets_FakeModificationSet implements Xinc_ModificationSet 
{
 	/**
 	 * Returns whether the source has been modified
 	 *
 	 * @return boolean
 	 */
	public function checkModified() 
  	{
    	return true;
  	}
}
?>