<?php
/**
 * Xinc - Continuous Integration.
 * Pear Package Task taken from phing http://phing.info
 *
 * PHP version 5
 *
 * @category Development
 * @package  Xinc
 * @author   Arno Schneider <username@example.org>
 * @author   Hans Lellelid <hans@xmpl.org>
 * @author   Alexander Opitz <opitz.alexander@gmail.com>
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *           This file is part of Xinc.
 *           Xinc is free software; you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as
 *           published by the Free Software Foundation; either version 2.1 of
 *           the License, or (at your option) any later version.
 *
 *           Xinc is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public
 *           License along with Xinc, write to the Free Software Foundation,
 *           Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link     http://code.google.com/p/xinc
 */

require_once 'phing/tasks/ext/pearpackage/Fileset.php';
require_once 'PEAR.php';
require_once 'PEAR/Frontend.php';
require_once 'PEAR/Task/Postinstallscript/rw.php';

/**
 * The package task.
 *
 * @category Development
 * @package  Xinc
 * @author   Arno Schneider <username@example.org>
 * @author   Hans Lellelid <hans@xmpl.org>
 * @author   Alexander Opitz <opitz.alexander@gmail.com>
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 * @link     http://code.google.com/p/xinc
 */
class XincPEARPackageTask extends MatchingTask
{
    /** @var PhingFile Base directory for reading files. */
    private $dir;

    /** @var string Versionnumber for this build. */
    private $version;

    /** @var string Version state for this build. */
    private $state = 'alpha';

    /** @var string . */
    private $notes;

    /** @var array of FileSet. */
    private $arFilesets = array();

    /** @var PhingFile Package file */
    private $packageFile;

    public function init()
    {
        $ret = @include_once 'PEAR/PackageFileManager2.php';
        if (false === $ret) {
            throw new BuildException(
                'You must have installed PEAR_PackageFileManager2'
                . 'in order to create a PEAR package.xml file.'
            );
        }
    }

    /**
     * Main entry point.
     *
     * @return void
     * @throws BuildException
     */
     public function main()
     {
        $this->checkPreConditions();

        // @var PEAR_PackageFileManager2
        $package = new PEAR_PackageFileManager2();

        $package->setOptions($this->getOptions());

        // the hard-coded stuff
        $package->setPackage('Xinc');
        $package->setSummary('Xinc - Continuous Integration Server');
        $package->setDescription(
            'Xinc is a continuous integration server written in PHP 5.'
            . ' It has built-in support for Subversion and Phing (and therefore'
            . ' PHPUnit), and can be easily extended to work with alternative'
            . ' version control or build tools.'
        );
        $package->setChannel('pear.elektrischeslicht.de');
        $package->setPackageType('php');

        $package->setReleaseVersion($this->version);
        $package->setAPIVersion($this->version);

        $package->setReleaseStability($this->state);
        $package->setAPIStability($this->state);

        $package->setNotes($this->notes);

        $package->setLicense('LGPL', 'http://www.gnu.org/licenses/lgpl.html');

        // Add package maintainers
        $package->addMaintainer('lead', 'arnoschn', 'Arno Schneider', 'arnoschn@gmail.com');
        $package->addMaintainer('lead', 'opi', 'Alexander Opitz', 'opitz.alexander@gmail.com');
        $package->addMaintainer('lead', 'gavinleefoster', 'Gavin Foster', 'gavinleefoster@gmail.com', 'no');

        // (wow ... this is a poor design ...)
        //
        // note that the order of the method calls below is creating
        // sub-"release" sections which have specific rules.  This replaces
        // the platformexceptions system in the older version of PEAR's package.xml
        //
        // Programmatically, I feel the need to re-iterate that this API for PEAR_PackageFileManager
        // seems really wrong.  Sub-sections should be encapsulated in objects instead of having
        // a "flat" API that does not represent the structure being created....

        $this->addSubsectionWindows($package);
        $this->addSubsectionOthers($package);

        // "core" dependencies
        $package->setPhpDep('5.2.7');
        $package->setPearinstallerDep('1.4.0');

        // "package" dependencies
        $package->addExtensionDep('required', 'xsl');
        $package->addExtensionDep('required', 'xml');
        $package->addPackageDepWithChannel('required', 'phing', 'pear.phing.info', '2.4.0');
        $package->addPackageDepWithChannel('required', 'Base', 'components.ez.no', '1.4.1');
        $package->addPackageDepWithChannel('required', 'Graph', 'components.ez.no', '1.2.1');
        $package->addPackageDepWithChannel('optional', 'VersionControl_SVN', 'pear.php.net', '0.5.0');
        $package->addPackageDepWithChannel('optional', 'VersionControl_Git', 'pear.php.net', '0.4.4');
        $package->addPackageDepWithChannel('optional', 'Mail', 'pear.php.net', '1.2.0');
        $package->addPackageDepWithChannel('optional', 'PHPUnit', 'pear.phpunit.de', '3.5.0');
        $package->addPackageDepWithChannel('optional', 'PhpDocumentor', 'pear.php.net', '1.4.0');
        $package->addPackageDepWithChannel('optional', 'PHP_CodeSniffer', 'pear.php.net', '1.3.0');
        $package->addPackageDepWithChannel('optional', 'Xdebug', 'pecl.php.net', '2.0.0');
        $package->addPackageDepWithChannel('optional', 'Archive_Tar', 'pear.php.net', '1.3.0');
        $package->addPackageDepWithChannel('optional', 'PEAR_PackageFileManager2', 'pear.php.net', '1.0.2');

        // now add the replacements ....
        $package->addReplacement('bin/xinc.bat', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('bin/xinc-settings.bat', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('bin/xinc-settings.bat', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('Xinc.php', 'package-info', '@VERSION@', 'version');
        $package->addReplacement('xinc.ini.tpl', 'package-info', '@VERSION@', 'version');
        $package->addReplacement('bin/xinc.bat', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('scripts/xinc-uninstall', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('scripts/xinc-uninstall.bat', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('scripts/xinc-uninstall', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('scripts/xinc-uninstall.bat', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('etc/init.d/xinc.bat', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('etc/init.d/xinc', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('bin/xinc', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('bin/xinc-settings', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('bin/xinc-builds', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('Xinc/scripts/pear-install.sh', 'pear-config', '@data-dir@','data_dir');

        $config = PEAR_Config::singleton();
        $log = PEAR_Frontend::singleton();
        $task = new PEAR_Task_Postinstallscript_rw(
            $package, $config, $log,
            array('name' => 'Xinc/Postinstall/Nix.php', 'role' => 'php')
        );
        $task->addParamGroup(
            'daemoninstall',
            array(
                $task->getParam('etc_dir', 'Directory to keep the Xinc config files', 'string', '/etc/xinc'),
                $task->getParam('xinc_dir', 'Directory to keep the Xinc Projects and Status information', 'string', '/var/xinc'),
                $task->getParam('log_dir', 'Directory to keep the Xinc log files', 'string', '/var/log'),
                $task->getParam('initd_dir', 'Directory to install the Xinc start/stop daemon', 'string', '/etc/init.d'),
                $task->getParam('tmp_dir', 'Directory for xinc`s temporary files', 'string', '/tmp/xinc'),
                $task->getParam('install_examples', 'Do you want to install the SimpleProject example', 'string', 'yes'),
                $task->getParam('www_dir', 'Directory to install the Xinc web-application', 'string', '/var/www/xinc'),
                $task->getParam('www_ip', 'IP of Xinc web-application', 'string', '127.0.0.1'),
                $task->getParam('www_port', 'Port of Xinc web-application', 'string', '8080'),
            )
        );
        $package->addPostinstallTask($task, 'Xinc/Postinstall/Nix.php');

        $taskWin = new PEAR_Task_Postinstallscript_rw(
            $package, $config, $log,
            array('name' => 'Xinc/Postinstall/Win.php', 'role' => 'php')
        );
        $taskWin->addParamGroup(
            'daemoninstall',
            array(
                $taskWin->getParam('etc_dir', 'Directory to keep the Xinc config files', 'string', 'C:\\xinc\\etc'),
                $taskWin->getParam('xinc_dir', 'Directory to keep the Xinc Projects and Status information', 'string', 'C:\\xinc'),
                $taskWin->getParam('log_dir', 'Directory to keep the Xinc log files', 'string', 'C:\\xinc\\log'),
                $taskWin->getParam('initd_dir', 'Directory to install the Xinc start/stop script', 'string', 'C:\\xinc\\init.d'),
                $taskWin->getParam('tmp_dir', 'Directory for xinc`s temporary files', 'string', 'C:\\xinc\\temp'),
                $taskWin->getParam('install_examples', 'Do you want to install the SimpleProject example', 'string', 'yes'),
                $taskWin->getParam('www_dir', 'Directory to install the Xinc web-application', 'string', 'C:\\xinc\\www'),
                $taskWin->getParam('www_ip', 'IP of Xinc web-application', 'string', '127.0.0.1'),
                $taskWin->getParam('www_port', 'Port of Xinc web-application', 'string', '8080'),
            )
        );
        $package->addPostinstallTask($taskWin, 'Xinc/Postinstall/Win.php');

        // now we run this weird generateContents() method that apparently
        // is necessary before we can add replacements ... ?
        $package->generateContents();

        $e = $package->writePackageFile();

        if (PEAR::isError($e)) {
            throw new BuildException(
                'Unable to write package file.',
                new Exception($e->getMessage())
            );
        }
    }

    /**
     * Checks if needed parts are available. If not it throws an exception.
     *
     * @return void
     * @throws BuildException
     */
    private function checkPreConditions()
    {
        if (empty($this->arFilesets)) {
            throw new BuildException(
                'You must use a <fileset> tag to specify the files'
                . 'to include in the package.xml'
            );
        }

        if ($this->dir === null) {
            throw new BuildException(
                'You must specify the "dir" attribute for PEAR package task.'
            );
        }

        if ($this->version === null) {
            throw new BuildException(
                'You must specify the "version" attribute for PEAR package task.'
            );
        }
    }

    /**
     * Sets the options in the package manager
     *
     * @return array Option array for package manager.
     */
    private function getOptions()
    {
        $arOptions = array(
            'baseinstalldir' => '/',
            'packagedirectory' => $this->dir->getAbsolutePath(),
            'filelistgenerator' => 'Fileset',
            'dir_roles' => array(
                'bin'       => 'script',
                'templates' => 'data',
                'examples'  => 'data',
                'resources' => 'data',
                'etc'       => 'data',
                'scripts'   => 'data',
                'web'       => 'data',
                'tests'     => 'test',
            ),
            // options needed by phing Fileset reader
            'phing_project' => $this->getProject(),
            'phing_filesets' => $this->arFilesets,
        );

        if ($this->packageFile !== null) {
            // create one w/ full path
            $f = new PhingFile($this->packageFile->getAbsolutePath());
            $arOptions['packagefile'] = $f->getName();
            // must end in trailing slash
            $arOptions['outputdirectory'] = $f->getParent() . DIRECTORY_SEPARATOR;
            $this->log("Creating package file: " . $f->getPath(), PROJECT_MSG_INFO);
        } else {
            $this->log("Creating [default] package.xml file in base directory.", PROJECT_MSG_INFO);
        }

        return $arOptions;
    }

    /**
     * Adds Windows release to the package manager
     *
     * @param PEAR_PackageFileManager2 $package The package manager.
     *
     * @return void
     */
    private function addSubsectionWindows(PEAR_PackageFileManager2 $package)
    {
        // windows release
        $package->addRelease();
        $package->setOSInstallCondition('windows');

        $package->addInstallAs('bin/xinc.bat', 'xinc.bat');
        $package->addInstallAs('bin/xinc.php', 'xinc.php');
        $package->addInstallAs('bin/xinc-settings.bat', 'xinc-settings.bat');
        $package->addInstallAs('bin/xinc-settings.php', 'xinc-settings.php');

        $package->addIgnoreToRelease('Xinc/Postinstall/Nix.php');
        $package->addIgnoreToRelease('bin/xinc');
        $package->addIgnoreToRelease('bin/xinc-settings');
        $package->addIgnoreToRelease('scripts/xinc-uninstall');
        $package->addIgnoreToRelease('bin/xinc-builds');
        $package->addIgnoreToRelease('etc/init.d/xinc');
    }


    /**
     * Adds Others release to the package manager
     *
     * @param PEAR_PackageFileManager2 $package The package manager.
     *
     * @return void
     */
    private function addSubsectionOthers(PEAR_PackageFileManager2 $package)
    {
        // Linux release
        $package->addRelease();

        $package->addInstallAs('bin/xinc', 'xinc');
        $package->addInstallAs('bin/xinc-settings', 'xinc-settings');
        $package->addInstallAs('bin/xinc-builds', 'xinc-builds');

        $package->addIgnoreToRelease('Xinc/Postinstall/Win.php');
        $package->addIgnoreToRelease('bin/xinc.bat');
        $package->addIgnoreToRelease('bin/xinc.php');
        $package->addIgnoreToRelease('etc/init.d/xinc.bat');
        $package->addIgnoreToRelease('scripts/winserv.exe');
        $package->addIgnoreToRelease('scripts/xinc-uninstall.bat');
    }

    /**
     * Used by the PEAR_PackageFileManager_FileSet lister.
     *
     * @return array FileSet[]
     */
    public function getFileSets()
    {
        return $this->arFilesets;
    }

    // -------------------------------
    // Set properties from XML
    // -------------------------------

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @return FileSet The created fileset object
     */
    function createFileSet()
    {
        $fileset = new FileSet();
        array_push($this->arFilesets, $fileset);
        return $fileset;
    }

    /**
     * Set the version we are building.
     *
     * @param string $v
     *
     * @return void
     */
    public function setVersion($v)
    {
        $this->version = $v;
    }

    /**
     * Set the state we are building.
     *
     * @param string $v
     *
     * @return void
     */
    public function setState($v)
    {
        $this->state = $v;
    }

    /**
     * Sets release notes field.
     *
     * @param string $v
     *
     * @return void
     */
    public function setNotes($v)
    {
        $this->notes = $v;
    }

    /**
     * Sets "dir" property from XML.
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setDir(PhingFile $f)
    {
        $this->dir = $f;
    }

    /**
     * Sets the file to use for generated package.xml
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setDestFile(PhingFile $f)
    {
        $this->packageFile = $f;
    }
}
