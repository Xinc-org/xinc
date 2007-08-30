<?php
/**
 *	
 * This is the main parser that constructs a Project instance from the config file.
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
require_once("Xinc_Project.php");

class Xinc_Parser 
{
	/**
	 * 
	 * @param configFile - the filename to build the project from.
	 * @return Project - the project built from the configFile xml file.
	 */
	public function parse($configFile) 
	{
		$project = new Xinc_Project();
		$xml = new SimpleXMLElement(file_get_contents($configFile));

		// parse modification sets..
		foreach ($xml->modificationSets->children() as $set) {
  			$className = "Xinc_ModificationSets_".$set->getName();  
  			$modificationSetObject = new $className();
  
			foreach($set->attributes() as $a=>$b) {
				$setter = "set$a";
    			$modificationSetObject->$setter($b);
  			}

  			$project->addModificationSet($modificationSetObject);
  
		}

		foreach ($xml->publishers->children() as $publisher) {
  			$className = "Xinc_Publishers_".$publisher->getName();  
  			$publisherObject = new $className();
  			foreach($publisher->attributes() as $a=>$b) {
    			$setter = "set$a";
  				$publisherObject->$setter($b);
  			}
  			$project->addPublisher($publisherObject);  
		}

		$phingBuilder = new Xinc_PhingBuilder();
		foreach($xml->phingbuild->attributes() as $a=>$b) {
			$setter = "set$a";
  			$phingBuilder->$setter($b);
		}

		$project->setPhingBuilder($phingBuilder);
		$project->setInterval($xml['interval']);
		$project->setName($xml['name']);
		
		return $project;
	}
}
		

?>