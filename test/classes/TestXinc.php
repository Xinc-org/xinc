<?php
require_once 'Xinc.php';
require_once 'Xinc/Exception/MalformedConfig.php';
require_once 'Xinc/Logger.php';
require_once 'Xinc/ModificationSet/Fake.php';
require_once 'Xinc/Builder/Fake.php';

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Xinc_TestXinc extends  PHPUnit_Framework_TestCase
{
	private $xinc;
	private $project;
	private $modificationSet;

	function setUp() 
	{
		$this->xinc = new Xinc();
		$this->xinc->setStatusDir('projects');
		// setup some projects
		
		$this->project = new Xinc_Project();
		$this->project->setInterval(10);
		$this->project->setName('Test Project 1');
	
		$this->xinc->addProject($this->project);	
	
		//
		$this->modificationSet = new Xinc_ModificationSet_Fake();
		$this->modificationSet->setModified(true);
	}
	
	function tearDown() 
	{
		
	}
	
	function testCheckProjectForceExpire() 
	{
		// can/should we check log output?	
		$this->xinc->checkProject($this->project);
		// should not have expired..
		
		// force to expire	
		$this->project->setInterval(-10);
		$this->project->reschedule();
		
		$this->xinc->checkProject($this->project);

		//
	}

	/**
	 * need to force the function to check that 
	 * the modification set is good..
	 */
	function testCheckProjectForceModifiedNullBuilder()
	{
		$this->project->addModificationSet($this->modificationSet);
		$this->project->setInterval(-10);
		$this->project->reschedule();		
		
		try {
			$this->xinc->checkProject($this->project);
		}
		catch(Xinc_Exception_MalformedConfig $exception){
			return;
		}

		//oops the builder is null..
		$this->fail("expected Xinc_Exception_MalformedConfig to be thrown");
	}

	function testCheckProjectForceModified()
	{
		$fakeProjectBuilder = new Xinc_Builder_Fake(true);
		$this->assertFalse($fakeProjectBuilder->getBuildCalled(), 'confirming that the build has not been called');

		$this->project->addModificationSet($this->modificationSet);
		$this->project->setInterval(-10);
		$this->project->reschedule();		
		$this->project->setBuilder($fakeProjectBuilder);

		$this->xinc->checkProject($this->project, 'confirming that the build has been called');
		$this->assertTrue($fakeProjectBuilder->getBuildCalled());
	}


	
	
}
