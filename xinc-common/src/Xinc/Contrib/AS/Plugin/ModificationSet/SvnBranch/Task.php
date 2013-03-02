<?php
/**
 * task to define which branches/tags should trigger a build
 *
 * @package Xinc.Contrib
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2008 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
require_once 'Xinc/Plugin/Repos/ModificationSet/AbstractTask.php';
require_once 'Xinc/Contrib/Warko/Plugin/ModificationSet/SvnTag/Task.php';

class Xinc_Contrib_AS_Plugin_ModificationSet_SvnBranch_Task extends Xinc_Contrib_Warko_Plugin_ModificationSet_SvnTag_Task
{
   
    public function getName(){
         return "svnbranch";
    }
    
    public function setBranchNameProperty($value)
    {
        $this->_tagNameProperty = $value;
    }
}
