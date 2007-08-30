<?php
/**
 * This is the main parser that constructs a Project instance from the config file.
 * 
 * @package Xinc
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
require_once 'Xinc/Project.php';

class Xinc_Parser 
{
	/**
	 * Public parse function 
	 * 
	 * @throws Xinc_Exception_MalformedConfig
	 */	
	public function parse($configFile) 
	{
		try {
			return $this->_parse($configFile);
		}
		catch(Exception $e) {
			throw new Xinc_Exception_MalformedConfig();
		}	
	}

	/**
	 * Parse the Xinc config file.
	 * 
	 * @param configFile
	 * @return Xinc_Project
	 */
	private function _parse($configFile) 
	{
		$project = new Xinc_Project();
		$xml = new SimpleXMLElement(file_get_contents($configFile));

		$projects = array();
		foreach ($xml->project as $projXml) {
			foreach ($projXml->modificationsets->children() as $set) {
			    require_once('Xinc/ModificationSet/'.ucfirst($set->getName()).'.php');
  				$className = 'Xinc_ModificationSet_'.$set->getName();
  				$modificationSetObject = new $className();
				foreach($set->attributes() as $a=>$b) {
					$setter = "set$a";
					$modificationSetObject->$setter($b);
				}
  				$modificationSetObject->validate();
  				$project->addModificationSet($modificationSetObject);
			}

			foreach ($projXml->publishers->children() as $publisher) {
			    require_once('Xinc/Publisher/'.ucfirst($publisher->getName()).'.php');
  				$className = "Xinc_Publisher_".$publisher->getName();
  				$publisherObject = new $className();
  				foreach($publisher->attributes() as $a=>$b) {
    				$setter = "set$a";
  					$publisherObject->$setter($b);
  				}
  				$publisherObject->validate();
  				$project->addPublisher($publisherObject);  
			}

			require_once('Xinc/Builder/'.ucfirst($projXml->builder['type']).'.php');
			$builderClass = 'Xinc_Builder_' . ucfirst($projXml->builder['type']);
			$builder = new $builderClass();
			foreach($projXml->builder->attributes() as $name => $value) {
			    if (strtolower($name) != 'type') {
				    $setter = "set$name";
  				    $builder->$setter($value);
			    }
			}
			$builder->validate();

			$project->setBuilder($builder);
			$project->setInterval($projXml['interval']);
			$project->setName($projXml['name']);
			
			$projects[] =  $project;
		}
		return $projects;
	}
}