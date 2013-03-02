<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Page.php';

class PageTest extends PHPUnit_Framework_TestCase
{
    public function testGetOutput()
    {
        $page = new Page();
        $this->assertNotEquals(0, strlen($page->getOutput()));
    }
}
