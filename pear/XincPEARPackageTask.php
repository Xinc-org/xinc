<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

include_once 'phing/tasks/ext/pearpackage/Fileset.php';
include_once 'PEAR.php';
include_once 'PEAR/Frontend.php';
include_once 'PEAR/Task/Postinstallscript/rw.php';

/**
 *
 * @author   Arno Schneider
 * @author   Hans Lellelid <hans@xmpl.org>
 * @package  phing.tasks.ext
 * @version  $Revision$
 */
class XincPEARPackageTask extends MatchingTask
{
    /** Base directory for reading files. */
    private $dir;

    private $version;
    private $state = 'beta';
    private $notes;
    private $_outputdir;
    private $filesets = array();

    /** Package file */
    private $packageFile;

    public function init() {
        include_once 'PEAR/PackageFileManager2.php';
        if (!class_exists('PEAR_PackageFileManager2')) {
            throw new BuildException("You must have installed PEAR_PackageFileManager2 (PEAR_PackageFileManager >= 1.6.0) in order to create a PEAR package.xml file.");
        }
    }

    private function setOptions($pkg)
    {
        $options['baseinstalldir'] = '/';
        $options['packagedirectory'] = $this->dir->getAbsolutePath();

        if (empty($this->filesets)) {
            throw new BuildException("You must use a <fileset> tag to specify the files to include in the package.xml");
        }

        $options['filelistgenerator'] = 'Fileset';

        // Some PHING-specific options needed by our Fileset reader
        $options['phing_project'] = $this->getProject();
        $options['phing_filesets'] = $this->filesets;

        if ($this->packageFile !== null) {
            // create one w/ full path
            $f = new PhingFile($this->packageFile->getAbsolutePath());
            $options['packagefile'] = $f->getName();
            // must end in trailing slash
            $options['outputdirectory'] = $f->getParent() . DIRECTORY_SEPARATOR;
            $this->_outputdir = $f->getParent() . DIRECTORY_SEPARATOR;
            $this->log("Creating package file: " . $f->getPath(), PROJECT_MSG_INFO);
        } else {
            $this->log("Creating [default] package.xml file in base directory.", PROJECT_MSG_INFO);
        }

        // add install exceptions
        //$options['installexceptions'] = array('Xinc/Postinstall.php'=>'script');

        $options['dir_roles'] = array(
            'bin'       => 'script',
            'templates' => 'data',
            'scripts'   => 'data',
            'examples'  => 'data',
            'resources' => 'data',
            'etc'       => 'data',
            'web'       => 'data'
        );

        //$options['exceptions'] = array('Xinc/Postinstall.php'=>'script');
        $options['scriptphaseexceptions'] = array ('Xinc/Postinstall.php' => 'postinstall');
        $pkg->setOptions($options);
    }

    /**
     * Main entry point.
     *
     * @return void
     */
     public function main()
     {
        if ($this->dir === null) {
            throw new BuildException("You must specify the \"dir\" attribute for PEAR package task.");
        }

        if ($this->version === null) {
            throw new BuildException("You must specify the \"version\" attribute for PEAR package task.");
        }

        $package = new PEAR_PackageFileManager2();

        $this->setOptions($package);

        // the hard-coded stuff
        $package->setPackage('Xinc');
        $package->setSummary('Xinc summary');
        $package->setDescription('Xinc');
        $package->setChannel('pear.php.net');
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

        // creating a sub-section for 'windows'

        // Linux release
        $package->addRelease();
        /**
         * for everything but windows, we will use the following release files
         */
        $package->setOSInstallCondition('windows', true);
        //$package->setArchInstallCondition('(linux|unix)');
        $package->addIgnoreToRelease('Xinc/Postinstall/Win.php');
        //$package->addIgnoreToRelease('bin/srvany.exe');
        //$package->addIgnoreToRelease('bin/instsrv.exe');
        $package->addIgnoreToRelease('bin/xinc.bat');
        $package->addIgnoreToRelease('bin/xinc.php');
        $package->addIgnoreToRelease('etc/init.d/xinc.bat');
        $package->addIgnoreToRelease('scripts/xinc-uninstall.bat');
        $package->addInstallAs('bin/xinc', 'xinc');
        $package->addInstallAs('bin/xinc-settings', 'xinc-settings');
        $package->addInstallAs('bin/xinc-builds', 'xinc-builds');
        // windows release
        $package->addRelease();
        $package->setOSInstallCondition('windows');
        //$package->addInstallAs('Xinc/bin/xinc', 'xinc.php');
        $package->addIgnoreToRelease('Xinc/Postinstall/Nix.php');
        $package->addIgnoreToRelease('bin/xinc');
        $package->addIgnoreToRelease('bin/xinc-settings');
        $package->addIgnoreToRelease('scripts/xinc-uninstall');
        $package->addIgnoreToRelease('bin/xinc-builds');
        $package->addInstallAs('bin/xinc.bat', 'xinc.bat');
        $package->addInstallAs('bin/xinc.php', 'xinc.php');
        $package->addInstallAs('bin/xinc-settings.bat', 'xinc-settings.bat');
        $package->addInstallAs('bin/xinc-settings.php', 'xinc-settings.php');
        $package->addIgnoreToRelease('etc/init.d/xinc');
        //$package->addInstallAs('bin/instsrv.exe', 'instsrv.exe');
        //$package->addInstallAs('bin/srvany.exe', 'srvany.exe');
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
        // creating a sub-section for non-windows

        //$package->setOSInstallCondition('(*ix|*ux|darwin*|*BSD|SunOS*)');

        /**$package->addInstallAs('bin/pear-phing', 'phing');
         $package->addIgnoreToRelease('bin/pear-phing.bat');*/

        // "core" dependencies
        $package->setPhpDep('5.0.3');
        $package->setPearinstallerDep('1.4.0');

        // "package" dependencies
        $package->addExtensionDep('required', 'xsl');
        $package->addExtensionDep('required', 'xml');
        $package->addPackageDepWithChannel( 'required', 'phing', 'pear.phing.info', '2.2.0');
        $package->addPackageDepWithChannel( 'required', 'VersionControl_SVN', 'pear.php.net', '0.3.0alpha1');
        $package->addPackageDepWithChannel( 'required', 'Base', 'components.ez.no', '1.4.1');
        $package->addPackageDepWithChannel( 'required', 'Graph', 'components.ez.no', '1.2.1');
        $package->addPackageDepWithChannel( 'optional', 'Mail', 'pear.php.net', '1.1.3');
        $package->addPackageDepWithChannel( 'optional', 'PHPUnit', 'pear.phpunit.de', '2.3.0');
        $package->addPackageDepWithChannel( 'optional', 'PhpDocumentor', 'pear.php.net', '1.3.0RC3');
        $package->addPackageDepWithChannel( 'optional', 'PHP_CodeSniffer', 'pear.php.net', '1.0.0RC3');
        $package->addPackageDepWithChannel( 'optional', 'Xdebug', 'pear.php.net', '2.0.0');
        $package->addPackageDepWithChannel( 'optional', 'Archive_Tar', 'pear.php.net', '1.3.0');
        $package->addPackageDepWithChannel( 'optional', 'PEAR_PackageFileManager', 'pear.php.net', '1.5.2');

        // now add the replacements ....

        $package->addReplacement('bin/xinc', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('bin/xinc-settings', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('bin/xinc-builds', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('Xinc/scripts/pear-install.sh', 'pear-config', '@data-dir@','data_dir');
        $config = PEAR_Config::singleton();
        $log = PEAR_Frontend::singleton();
        $task = new PEAR_Task_Postinstallscript_rw($package, $config, $log,
        array('name' => 'Xinc/Postinstall/Nix.php', 'role' => 'php'));
        $task->addParamGroup('daemoninstall', array(
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


        $taskWin = new PEAR_Task_Postinstallscript_rw($package, $config, $log,
        array('name' => 'Xinc/Postinstall/Win.php', 'role' => 'php'));
        $taskWin->addParamGroup('daemoninstall', array(
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
            throw new BuildException("Unable to write package file.", new Exception($e->getMessage()));
        }
    }

    /**
     * Used by the PEAR_PackageFileManager_FileSet lister.
     *
     * @return array FileSet[]
     */
    public function getFileSets() {
        return $this->filesets;
    }

    // -------------------------------
    // Set properties from XML
    // -------------------------------

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @return FileSet The created fileset object
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * Set the version we are building.
     *
     * @param string $v
     *
     * @return void
     */
    public function setVersion($v){
        $this->version = $v;
    }

    /**
     * Set the state we are building.
     *
     * @param string $v
     *
     * @return void
     */
    public function setState($v) {
        $this->state = $v;
    }

    /**
     * Sets release notes field.
     *
     * @param string $v
     *
     * @return void
     */
    public function setNotes($v) {
        $this->notes = $v;
    }

    /**
     * Sets "dir" property from XML.
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setDir(PhingFile $f) {
        $this->dir = $f;
    }

    /**
     * Sets the file to use for generated package.xml
     *
     * @param PhingFile $f
     *
     * @return void
     */
    public function setDestFile(PhingFile $f) {
        $this->packageFile = $f;
    }
}
