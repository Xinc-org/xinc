<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category Development
 * @package  Xinc.Postinstall
 * @author   David Ellis <username@example.com>
 * @author   Gavin Foster <username@example.com>
 * @author   Arno Schneider <username@example.com>
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
 * @link     http://xincplus.sourceforge.net
 */

abstract class Xinc_Postinstall
{
    protected $_pkg;
    protected $_ui;
    protected $_config;
    protected $_registry;
    public $lastversion;
    public $pearDataDir;
    protected $_undoTasks = array();
    protected $_undoFiles = array();
    protected $_undoDirs = array();
    protected $_uninstallFiles = array();
    protected $_uninstallDirs = array();
    protected $_iniFileExists=false;
    
    public function init(&$config, &$pkg, $lastversion)
    {
        $this->_config = &$config;
        
        $this->_registry = &$config->getRegistry();
        $this->_ui = &PEAR_Frontend::singleton();
        $this->_pkg = &$pkg;
        $this->pearDataDir = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc';
        if (file_exists($this->pearDataDir . DIRECTORY_SEPARATOR . 'xinc.ini')) {
            
            $this->_iniFileExists=true;
        }
        $this->lastversion = $lastversion;
        return true;
    }
    
    public function postProcessPrompts($prompts, $section)
    {
        if ($this->_iniFileExists) {
            @include_once('Xinc/Ini.php');
            if (class_exists('Xinc_Ini')) {
                try {
                    $xincIni = Xinc_Ini::getInstance();
                } catch (Exception $e) {
                    $this->_ui->outputData('Could not initiate xinc.ini configuration');
                    return $this->_failedInstall();
                }
                $xincIni->set('version', $this->_pkg->getVersion(),'xinc');
                
                foreach ($prompts as $k=>$item) {
                    
                    switch($item['name']) {
                        case 'etc_dir':
                            $val=$xincIni->get('etc','xinc');
                            $prompts[$k]['default']=!empty($val)?$val:$prompts[$k]['default'];
                            break;
                        case 'xinc_dir':
                            $val=$xincIni->get('dir','xinc');
                            $prompts[$k]['default']=!empty($val)?$val:$prompts[$k]['default'];
                            break;
                        case 'tmp_dir':
                            $prompts[$k]['default'] = $this->_config->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc' . DIRECTORY_SEPARATOR . 'tmp';
                            break;
                        default:
                            $val = $xincIni->get($item['name'],'xinc');
                            $prompts[$k]['default']=!empty($val)?$val:$prompts[$k]['default'];
                            break;
                    }
                }
            }
        }
        return $prompts;
    }
    
    protected function _execCmd($cmd)
    {
        $out = null;
        $res = null;
        exec($cmd, $out, $res);
        return $res;
    }
    
    private function _createUninstallInfo()
    {
        $uninstallFileFile = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'xinc.uninstall.files';
        $uninstallDirFile = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'xinc.uninstall.dirs';
        $this->_uninstallFiles = array_reverse($this->_uninstallFiles);
        $this->_uninstallDirs = array_reverse($this->_uninstallDirs);
        file_put_contents($uninstallFileFile, implode("\n", $this->_uninstallFiles));
        file_put_contents($uninstallDirFile, implode("\n", $this->_uninstallDirs));
    }
    
    abstract protected function _platformSpecificInstall($etcDir, $logDir, $statusDir, $dataDir, $initDir);
    
    abstract protected function _createDir($dirName, $permission);
    
    abstract protected function _copyFiles($src, $target, $extra = '');
    
    abstract protected function _deleteFile($file, $extra='');
    abstract protected function _deleteDir($dir, $extra='');
    
    protected function _failedInstall()
    {
       
        foreach ($this->_undoFiles as $file) {
            $message=" Deleting file: $file:";
            $res = $this->_deleteFile($file);
            if ($res==0) {
                $message.="OK";
            } else {
                $message.="NOK";
            }
            $this->_ui->outputData($message);
        }
        foreach ($this->_undoDirs as $dir) {
            $message=" Deleting dir: $dir:";
            $res = $this->_deleteDir($dir);
            if ($res==0) {
                $message.="OK";
            } else {
                $message.="NOK";
            }
            $this->_ui->outputData($message);
        }
        PEAR::raiseError('[FAILED] Post installation. Rolling back');
        die();
    }
    
    public function run($answers, $phase)
    {
        
        
        switch($phase) {
            
            case 'daemoninstall':
                $pearDataDir = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc';
                $xincPhpDir = PEAR_Config::singleton()->get('php_dir') . DIRECTORY_SEPARATOR . 'Xinc';
                $pearPhpDir = PEAR_Config::singleton()->get('php_dir');
                $binDir = PEAR_Config::singleton()->get('bin_dir');
                if(!$this->_iniFileExists) {
                    $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR .'xinc.ini.tpl', $pearDataDir . DIRECTORY_SEPARATOR .'xinc.ini');
                    $this->_iniFileExists = true;
                }
                include_once $pearPhpDir . '/Xinc/Ini.php';
                if (class_exists('Xinc_Ini')) {
                    try {
                        $xincIni = Xinc_Ini::getInstance();
                    } catch (Exception $e) {
                        $this->_ui->outputData($e->getMessage());
                        $this->_failedInstall();
                    }
                } else {
                    $this->_ui->outputData('Cannot initialize Xinc_Ini class');
                    $this->_failedInstall();
                    return false;
                }
                $xincDir = $answers['xinc_dir'];
                $this->_createDir($xincDir, 0655);
                $xincDir = realpath($xincDir);
                $xincIni->set('dir', $xincDir, 'xinc');
                
                $etcDir = $answers['etc_dir'];
                $this->_createDir($etcDir, 0655);
                $etcDir = realpath($etcDir);
                $xincIni->set('etc', $etcDir, 'xinc');
                
                $tmpDir = $answers['tmp_dir'];
                $this->_createDir($tmpDir, 0777);
                $tmpDir = realpath($tmpDir);
                $xincIni->set('tmp_dir', $tmpDir, 'xinc');
                
                $etcConfDir = $etcDir . DIRECTORY_SEPARATOR . 'conf.d';
                $this->_createDir($etcConfDir, 0655);
                $etcConfDir = realpath($etcConfDir);
                $xincIni->set('etc_conf_d', $etcConfDir, 'xinc');
                
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR
                                 . 'xinc' . DIRECTORY_SEPARATOR . '*', $etcDir);
                
                $dataDir = $xincDir . DIRECTORY_SEPARATOR . 'projects';
                $this->_createDir($dataDir, 0655);
                $dataDir = realpath($dataDir);
                $xincIni->set('project_dir', $dataDir, 'xinc');
                
                $statusDir = $xincDir . DIRECTORY_SEPARATOR . 'status';
                $this->_createDir($statusDir, 0655);
                $statusDir = realpath($statusDir);
                $xincIni->set('status_dir', $statusDir, 'xinc');
                
                if (isset($answers['initd_dir'])) {
                    $initDir = $answers['initd_dir'];
                    $this->_createDir($initDir, 0655);
                    $initDir = realpath($initDir);
                }
                
                
                
                $logDir = $answers['log_dir'];
                $this->_createDir($logDir, 0655);
                $logDir = realpath($logDir);
                $xincIni->set('log_dir', $logDir, 'xinc');
                
                $xinclogDir = $logDir . DIRECTORY_SEPARATOR . 'xinc';
                
                $installExamples = $answers['install_examples'] == 'yes' ? true: false;
                
                if ($installExamples) {
                    $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR 
                                     . 'SimpleProject', $dataDir, '-Rf');
                    
                    /*$res = $this->_execCmd('cat '.$dataDir . '/SimpleProject/build.tpl.xml | sed -e "s#@EXAMPLE_DIR@#' 
                                          . $dataDir . '#" > '.$dataDir.'/SimpleProject/build.xml');*/
                    $res = $this->_execCat($dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'. DIRECTORY_SEPARATOR . 'build.tpl.xml',
                                $dataDir. DIRECTORY_SEPARATOR . 'SimpleProject' . DIRECTORY_SEPARATOR . 'build.xml',
                                array('@EXAMPLE_DIR@'=>$dataDir));
                    $this->_uninstallFiles[] = $dataDir. DIRECTORY_SEPARATOR . 'SimpleProject' . DIRECTORY_SEPARATOR . 'build.xml';
                    //$res = $this->_execCmd('rm ' . $dataDir . '/SimpleProject/build.tpl.xml');
                    $res = $this->_deleteFile($dataDir. DIRECTORY_SEPARATOR . 'SimpleProject' . DIRECTORY_SEPARATOR . 'build.tpl.xml');
                    /**$res = $this->_execCmd('cat '.$dataDir . '/SimpleProject/publish.tpl.xml | sed -e "s#@EXAMPLE_DIR@#'
                                          . $dataDir . '#" > '.$dataDir.'/SimpleProject/publish.xml');*/
                    $res = $this->_execCat($dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'. DIRECTORY_SEPARATOR . 'publish.tpl.xml',
                                $dataDir. DIRECTORY_SEPARATOR . 'SimpleProject' . DIRECTORY_SEPARATOR . 'publish.xml',
                                array('@EXAMPLE_DIR@'=>$dataDir));
                    $this->_uninstallFiles[] = $dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'. DIRECTORY_SEPARATOR . 'publish.xml';
                    $res = $this->_deleteFile($dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'. DIRECTORY_SEPARATOR . 'publish.tpl.xml');
                    /**$res = $this->_execCmd('cat ' . $pearDataDir
                                          . '/examples/simpleproject.tpl.xml | sed -e "s#@EXAMPLE_DIR@#'
                                          . $dataDir . '#" > '.$etcConfDir.'/simpleproject.xml');*/
                    $res = $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'examples'. DIRECTORY_SEPARATOR . 'simpleproject.tpl.xml',
                                $etcConfDir. DIRECTORY_SEPARATOR . 'simpleproject.xml',
                                array('@EXAMPLE_DIR@'=>$dataDir));
                    $this->_uninstallFiles[] = $etcConfDir. DIRECTORY_SEPARATOR . 'simpleproject.xml';
                    //$res = $this->_deleteFile($etcConfDir. DIRECTORY_SEPARATOR . 'simpleproject.tpl.xml');
                }
                //exec($pearDataDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear-install.sh');
                $wwwDir = $answers['www_dir'];
                $this->_createDir($wwwDir, 0755);
                $wwwDir = realpath($wwwDir);
                $xincIni->set('www_dir', $wwwDir, 'xinc');
                
                $wwwPort = $answers['www_port'];
                $xincIni->set('www_port', $wwwPort, 'xinc');
                $wwwIp =  $answers['www_ip'];
                $xincIni->set('www_ip', $wwwIp, 'xinc');
                
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'web'
                                 . DIRECTORY_SEPARATOR . '.htaccess', $wwwDir);
                
                
                
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'web'
                                 . DIRECTORY_SEPARATOR . '*', $wwwDir, '-Rf');
                $this->_deleteFile($wwwDir.'/www.tpl.conf');
                
                
                $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'web'. DIRECTORY_SEPARATOR . 'www.tpl.conf',
                                $etcDir.'/www.conf',
                                array('@INCLUDE@'=>$pearPhpDir, '@WEB_DIR@'=>$wwwDir,
                                '@PORT@'=>$wwwPort, '@IP@'=>$wwwIp));

                $this->_uninstallFiles[] = $etcDir . '/www.conf';
                $this->_undoFiles[] = $etcDir . '/www.conf';
                
                $this->_platformSpecificInstall($etcDir, $logDir, $statusDir, $dataDir, $initDir);
                
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'xinc-uninstall.php',
                     $binDir . DIRECTORY_SEPARATOR . 'xinc-uninstall.php');
                
                
                /**
                 * check for XAMPP
                 */
                $includePath = ini_get('include_path');
                $isXampp = false;
                if (stristr($includePath,'xampp')) {
                    $isXampp = true;
                    $this->_ui->outputData('############################## WARNING ##############################');
                    $this->_ui->outputData(' You are using a XAMPP installation.');
                    $this->_ui->outputData(' XAMPP uses a directory structure and an include_path configuration');
                    $this->_ui->outputData(' which causes problems with Xinc admin scripts.');
                    $this->_ui->outputData('');
                    $this->_ui->outputData(' Current include path: ' . $includePath);
                    $parts = split(';', $includePath);
                    $parts = array_reverse($parts);
                    if (!in_array('.', $parts)) {
                        $parts[] = '.';
                    }
                    $newIncludePath = join(';', $parts);
                    $this->_ui->outputData(' Please change to: ' . $newIncludePath);
                    $this->_ui->outputData('#####################################################################');
                    if (DIRECTORY_SEPARATOR != '/') {
                        $this->_ui->outputData('Setting phing path to: ' . $binDir . DIRECTORY_SEPARATOR . 'phing');
                        $xincIni->set('path', $binDir . DIRECTORY_SEPARATOR . 'phing', 'phing');
                    }
                }
                

                
                $this->_ui->outputData('Xinc installation complete.');
                $this->_ui->outputData("- Please include $etcDir/www.conf in your apache virtual hosts.");
                $this->_ui->outputData("- Please enable mod-rewrite.");
                $this->_ui->outputData("- To add projects to Xinc, copy the project xml to $etcConfDir");
                
                
                if (DIRECTORY_SEPARATOR != '/') {
                    $this->_ui->outputData("- To start xinc execute: winserv start Xinc");
                } else {
                    $this->_ui->outputData("- To start xinc execute: sudo $initDir/xinc start");
                }
                $this->_ui->outputData("UNINSTALL instructions:");
                $this->_ui->outputData("- pear uninstall xinc/Xinc");
                $this->_ui->outputData("- run: $binDir/xinc-uninstall to cleanup installed files");
                
                
                
                /**
                 * Handle uninstall info, write uninstall.ini into data dir
                 */
                $this->_createUninstallInfo();
                $res = $xincIni->save();
                if (!$res) {
                    $this->_ui->outputData("[ERROR] Could not save ini settings");
                } else {
                    $this->_ui->outputData("[OK] Saved ini settings");
                }
                return true;
                break;
            case '_undoOnError' :
                   
                break;
        }
        
    }
    
    protected function _execCat($src, $dest,  $replacements)
    {
        $contents = file_get_contents($src);
        $contents = str_replace(array_keys($replacements), array_values($replacements), $contents);
        file_put_contents($dest, $contents);
    }

}
