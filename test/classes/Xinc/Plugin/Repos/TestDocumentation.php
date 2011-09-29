<?php
/**
 * Test Class for the Xinc Plugin "Property"
 * 
 * @package Xinc.Plugin
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
require_once 'Xinc.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Engine/Sunrise.php';
require_once 'Xinc/Plugin/Repos/Documentation.php';
require_once 'Xinc/BaseTest.php';

class Xinc_Plugin_Repos_TestDocumentation extends Xinc_BaseTest
{
    public function setUp()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        $project = new Xinc_Project();
        $project->setName('SimpleProject');
        $plugin = new Xinc_Plugin_Repos_Documentation();
        $build = new Xinc_Build(new Xinc_Engine_Sunrise(),$project);
        $this->sharedFixture=array();
        $this->sharedFixture[0] = $build;
        $this->sharedFixture[1] = $plugin;

        $docDir = Xinc::getInstance()->getStatusDir() . DS . 'SimpleProject' . DS . 'docs';
        $this->sharedFixture[2] = $docDir;

        $docSubDir = $docDir . DS . 'sub';
        mkdir($docDir);
        mkdir($docSubDir);
        file_put_contents($docDir . DS . 'test.html', 'test');
        file_put_contents($docSubDir . DS . 'index.html', 'index');
    }

    public function tearDown()
    {
        $docDir = Xinc::getInstance()->getStatusDir() . DS . 'SimpleProject' . DS . 'docs';
        $docSubDir = $docDir . DS . 'sub';
        unlink($docSubDir . DS . 'index.html');
        unlink($docDir . DS . 'test.html');
        rmdir($docSubDir);
        rmdir($docDir);
    }

    public function testRegisterDocumentationDir()
    {
        try {
        $this->sharedFixture[1]->registerDocumentation($this->sharedFixture[0], $this->sharedFixture[2], 'test', $this->sharedFixture[2] . DS . 'sub' . DS . 'index.html');
        } catch (Exception $e) {
            var_dump($e);
        }
        $statusDir = Xinc::getInstance()->getStatusDir();
        $documentationDir = $statusDir . DS . $this->sharedFixture[0]->getStatusSubDir() . DS . 'documentation' . DS . 'test';
        $documentationDir = realpath($documentationDir);
        $docProp = $this->sharedFixture[0]->getInternalProperties()->get('documentation');
        //var_dump($docProp);
        $compareDir1 = $docProp['test']['file'];
        $compareDir = realpath($compareDir1);
        $this->assertEquals($documentationDir, $compareDir, 'Directories should be equal: ' . $documentationDir .'<>'. $compareDir1);
    }
    
    public function testRegisterDocumentationFile()
    {
        try {
        $this->sharedFixture[1]->registerDocumentation($this->sharedFixture[0], $this->sharedFixture[2] . DS . 'test.html', 'test', $this->sharedFixture[2] . DS . 'sub' . DS . 'index.html');
        } catch (Exception $e) {
            var_dump($e);
        }
        $statusDir = Xinc::getInstance()->getStatusDir();
        $documentationDir = $statusDir . DS . $this->sharedFixture[0]->getStatusSubDir() . DS . 'documentation' . DS . 'test' . DS . 'test.html';
        $documentationDir = realpath($documentationDir);
        $docProp = $this->sharedFixture[0]->getInternalProperties()->get('documentation');
        //var_dump($docProp);
        $compareDir1 = $docProp['test']['file'];
        $compareDir = realpath($compareDir1);
        $this->assertEquals($documentationDir, $compareDir, 'Files should be equal: ' . $documentationDir .'<>'. $compareDir1);
    }
   
}