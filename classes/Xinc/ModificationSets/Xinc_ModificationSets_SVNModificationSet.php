<?php
/**
 *	
 * This file represents the project to be continuously integrated
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

//require_once 'Xinc/Xinc_ModificationSet.php';


class Xinc_ModificationSets_SVNModificationSet implements Xinc_ModificationSet 
{
	private $svnCheckoutDirectory = ".";
  
	/**
	 * sets the svn checkout directory ..
	 * @param string - the svn checkout dir.
	 */
  	public function setSVNCheckoutDirectory($svnCheckoutDirectory)
  	{
  		$this->svnCheckoutDirectory = $svnCheckoutDirectory;
  	}
  
  	/**
   	* returns whether the source has been modified (from svn)..
   	* @return boolean -
   	*/
  	public function checkModified() 
  	{
  		global $logger;
 
  		$cwd = getcwd();
  		chdir($this->svnCheckoutDirectory);
    	$localSet = `svn info`;
    	$url = getURL($localSet);
    	$remoteSet = `svn info $url`;
    	$localRevision = getRevision($localSet);
    	$remoteRevision = getRevision($remoteSet);
   
    	$logger->debug("svn checkout dir is $this->svnCheckoutDirectory local revision @ $localRevision Remote Revision @ $remoteRevision \n");
    	
    	chdir($cwd);
    	
    	$logger->debug("modified ?? ".($localRevision<$remoteRevision));
    	
    	return ($localRevision<$remoteRevision);
  	}
}

// I'm hoping the new pear svn class will help this a bit?
function getURL($result) {
  $list = split("\n",$result);
  foreach ($list as $row) {
    $field = split(": ", $row);
    if (preg_match("/Author/",$field[0])) {
      $username = trim($field[1]);
      print ".$username.";
      $email = $emailList[$username];
      print "sending to $email\n\n";
    }
    if (preg_match("/URL/",$field[0])) {
      return trim($field[1]);
    }
  }
}

function getRevision($result) {
  $list = split("\n",$result);
   foreach ($list as $row) {
     $field = split(":", $row);
     if (preg_match("/Author/",$field[0])) {
       $username = trim($field[1]);
       print ".$username.";
       $email = $emailList[$username];
       print "sending to $email\n\n";
     }
     if (preg_match("/Revision/",$field[0])) {
       return trim($field[1]);
     }
  }
}



?>