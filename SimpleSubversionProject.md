# Simple Subversion Project #

For the [Xinc example](XincExample.md) we'll create a simple Subversion project with the following files:

```
 project/build.xml
 project/Page.php
 project/PageTest.php
 project/index.php
```

**build.xml** is a Phing build file that updates the Subversion project, and uses PHPUnit to test Page.php, before copying index.php and Page.php to /var/www/:

```
<?xml version="1.0"?>
<project name="Simple Project Build File" basedir="/var/projects/project" default="build">
    <target name="build" depends="update, test">
        <move file="index.php" tofile="/var/www/index.php" overwrite="true"/>
        <move file="Page.php" tofile="/var/www/Page.php" overwrite="true"/>
    </target>
    <target name="update">
        <exec command="svn update"/>
    </target>
    <target name="test">
        <phpunit haltonfailure="true" printsummary="true">
            <batchtest>
                <fileset dir=".">
                     <include name="*Test.php"/>
                </fileset>
            </batchtest>
        </phpunit>
    </target>
</project>
```

**Page.php** contains the Page class, which is a simple container for web page content:

```
<?php
class Page
{
    private $output;

    public function __construct()
    {
        $this->output = '<h1>Page output</h1>';
    }

    public function getOutput()
    {
        return $this->output;
    }
}
```

**PageTest.php** contains the PHPUnit tests for the Page class:

```
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
```

**index.php** uses the Page class to generate page output:

```
<?php
require_once 'Page.php';
$page = new Page();
echo $page->getOutput();
```

See how ths project is [continuously integrated with Xinc](XincExample.md).