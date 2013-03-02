<?php
/**
 * Test Class for the Xinc Build Queue
 * 
 * @package Xinc.Build
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
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
require_once 'Xinc/Build/Queue.php';
require_once 'Xinc/Engine/Sunrise.php';
require_once 'Xinc/Project.php';

require_once 'Xinc/BaseTest.php';

class Xinc_Build_TestQueue extends Xinc_BaseTest
{
    
   
    public function testOneBuildToBuild()
    {
        
        $build = new Xinc_Build(new Xinc_Engine_Sunrise(),new Xinc_Project());
        
        $queue = new Xinc_Build_Queue();
        $scheduler = new Xinc_Build_Scheduler_Default();
        
        $build->setScheduler($scheduler);
        
        
        $queue->addBuild($build);
        
        
        
        $nextBuildTime = $queue->getNextBuildTime();
        
        $this->assertTrue($nextBuildTime != null, 'We should have a default builttime');
        
        $nextBuild = $queue->getNextBuild();
        
        
        $this->assertEquals($build, $nextBuild, 'The Builds should be equal');
    }

    public function testOneBuildToBuildAddBuilds()
    {
        
        $build = new Xinc_Build(new Xinc_Engine_Sunrise(),new Xinc_Project());
        $buildIterator = new Xinc_Build_Iterator();
        $buildIterator->add($build);;
        $queue = new Xinc_Build_Queue();
        $scheduler = new Xinc_Build_Scheduler_Default();
        
        $build->setScheduler($scheduler);
        
        
        $queue->addBuilds($buildIterator);
        
        
        
        $nextBuildTime = $queue->getNextBuildTime();
        
        $this->assertTrue($nextBuildTime != null, 'We should have a default builttime');
        
        $nextBuild = $queue->getNextBuild();
        
        
        $this->assertEquals($build, $nextBuild, 'The Builds should be equal');
    }
   
}