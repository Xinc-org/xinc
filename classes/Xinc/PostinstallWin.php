<?php
class Xinc_PostinstallWin_postinstall
{
    private $_pkg;
    private $_ui;
    private $_config;
    private $_registry;
    public $db;
    public $user;
    public $password;
    public $lastversion;
    public $channel;
    public $alias;
    public $handle;
    public $docroot;
    public $port;
    public $ssl;
    public $xmlrpcphp;
    public $pearconfigloc;
    public $dbhost;
    public $databaseExists;
    public $fixHandles = false;
    private $_undoTasks = array();
    
    function init(&$config, &$pkg, $lastversion)
    {
        $this->_config = &$config;
        $this->_registry = &$config->getRegistry();
        $this->_ui = &PEAR_Frontend::singleton();
        $this->_pkg = &$pkg;
        $this->lastversion = $lastversion;
        return true;
    }

    function postProcessPrompts($prompts, $section)
    {
       
        foreach ($prompts as $item) {
            
        }
        /**switch ($section) {
            case 'channelCreate' :
                $prompts[0]['default'] = array_shift($a = explode('.', $this->channel));
            break;
            case 'files' :
                $conffile = $this->_config->getConfFile('user');
                if (!file_exists($conffile)) {
                    $conffile = $this->_config->getConfFile('system');
                }
                $prompts[0]['default'] = $conffile;
                $prompts[1]['prompt'] = sprintf($prompts[1]['prompt'], $this->channel);
            break;
        }*/
        return $prompts;
    }

    private function _createDir($dirName, $permission = 0777)
    {
      $this->_ui->outputData('Creating Directory '.$dirName);
        if (file_exists($dirName) || is_dir($dirName)) {
            if (!is_writeable($dirName)) {
                $this->_ui->outputData($dirName . ' is not writable');
                return $this->_failedInstall();
            }
        } else {
            $parentDir = dirname($dirName);
            
            if (!is_writeable($parentDir)) {
                $this->_ui->outputData($parentDir . ' is not writable');
                return $this->_failedInstall();
            }
            
            $res = mkdir($dirName, $permission, true);
            if (!$res) {
                $this->_ui->outputData('Could not create ' . $dirName);
                return $this->_failedInstall();
            }
            $this->_undoTasks[] = 'rmdir "' . $dirName . '" /s /q';
        }
        return true;
    }
    
    private function _copyFiles($src, $target, $extra = '')
    {
        $out = null;
        $res = null;
        $this->_ui->outputData('copy /y "' . $src . '" "' . $target .'"', $out, $res);
        exec('xcopy /E /Y "' . $src . '" "' . $target .'"', $out, $res);
        if ($res != 0) {
            $this->_ui->outputData('Could not copy "' . $src . '" to: ' . $target);
            return $this->_failedInstall();
        } else {
            $this->_undoTasks[] = 'rmdir "' . $target . '" /s /q';
            $this->_ui->outputData('Successfully copied ' . $src . '  to: ' . $target);
        }
        return true;
    }
    
    private function _execCmd($cmd)
    {
        $out = null;
        $res = null;
        exec($cmd, $out, $res);
        return $res;
    }
    
    function run($answers, $phase)
    {
        
        $pearDataDir = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc';
        $pearDataDir = realpath($pearDataDir);
        $xincPhpDir = PEAR_Config::singleton()->get('php_dir') . DIRECTORY_SEPARATOR . 'Xinc';
        $xincPhpDir = realpath($xincPhpDir);
        $pearPhpDir = PEAR_Config::singleton()->get('php_dir');
        $pearPhpDir = realpath($pearPhpDir);
        
        switch($phase) {
            
            case 'daemoninstall':
                $xincDir = $answers['xinc_dir'];
                
                $etcDir = $xincDir . DIRECTORY_SEPARATOR . 'etc';
                
                $etcConfDir = $etcDir . DIRECTORY_SEPARATOR . 'conf.d' ;
                
                $dataDir = $xincDir . DIRECTORY_SEPARATOR . 'projects';
                
                $statusDir = $xincDir . DIRECTORY_SEPARATOR . 'status';
                
                $this->_createDir($xincDir);
                $xincDir = realpath($xincDir);
                $this->_createDir($dataDir);
                $dataDir = realpath($dataDir);
                $this->_createDir($statusDir);
                $statusDir = realpath($statusDir);
                $this->_createDir($etcDir);
                $etcDir = realpath($etcDir);
                $this->_createDir($etcConfDir);
                $etcConfDir = realpath($etcConfDir);
                
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'etc'
                                 . DIRECTORY_SEPARATOR . 'xinc' . DIRECTORY_SEPARATOR
                                 . '*', $etcDir  . DIRECTORY_SEPARATOR);
                
               
                
                
                $initDir = $etcDir . DIRECTORY_SEPARATOR . 'init.d';
                $this->_createDir($initDir);
                $initDir = realpath($initDir);
                $installExamples = $answers['install_examples'] == 'yes' ? true: false;
                
                if ($installExamples) {
                    mkdir($dataDir . DIRECTORY_SEPARATOR . 'SimpleProject');
                    $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'examples'
                                     . DIRECTORY_SEPARATOR . 'SimpleProject' . DIRECTORY_SEPARATOR . '*',
                                     $dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'
                                     . DIRECTORY_SEPARATOR, '-Rf');
                }
                $logDir = $xincDir . DIRECTORY_SEPARATOR . 'log';
                $xinclogDir = $logDir . DIRECTORY_SEPARATOR . 'xinc';
                $this->_createDir($logDir);
                
                if ($installExamples) {
                    $res = $this->_execCat($dataDir . DIRECTORY_SEPARATOR .'SimpleProject'
                                          . DIRECTORY_SEPARATOR . 'build.tpl.xml',
                                          $dataDir.DIRECTORY_SEPARATOR  . 'SimpleProject'
                                          . DIRECTORY_SEPARATOR . 'build.xml', array('@EXAMPLE_DIR@'=>$dataDir));
                    unlink($dataDir. DIRECTORY_SEPARATOR . 'SimpleProject'.DIRECTORY_SEPARATOR.'build.tpl.xml');
                    $res = $this->_execCat($dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'
                                          . DIRECTORY_SEPARATOR . 'publish.tpl.xml',
                                          $dataDir . DIRECTORY_SEPARATOR . 'SimpleProject'
                                          . DIRECTORY_SEPARATOR . 'publish.xml', array('@EXAMPLE_DIR@'=>$dataDir));
                    unlink($dataDir.DIRECTORY_SEPARATOR.'SimpleProject'.DIRECTORY_SEPARATOR.'publish.tpl.xml');
                    $res = $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'examples'
                                          . DIRECTORY_SEPARATOR . 'simpleproject.tpl.xml',
                                          $etcConfDir . DIRECTORY_SEPARATOR.'simpleproject.xml',
                                          array('@EXAMPLE_DIR@'=>$dataDir));
                }
                //exec($pearDataDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear-install.sh');
                $wwwDir = $xincDir . DIRECTORY_SEPARATOR . 'www';
                $wwwPort = $answers['www_port'];
                $wwwIp =  $answers['www_ip'];
                $this->_createDir($wwwDir, 0755);
                $wwwDir = realpath($wwwDir);
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . '.htaccess',
                                  $wwwDir . DIRECTORY_SEPARATOR);
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . '*',
                                  $wwwDir . DIRECTORY_SEPARATOR, '-Rf');
                unlink($wwwDir.DIRECTORY_SEPARATOR.'www.tpl.conf');
                unlink($wwwDir.DIRECTORY_SEPARATOR.'handler.php.tpl');
                
                $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'web'. DIRECTORY_SEPARATOR . 'www.tpl.conf',
                                $etcDir.'/www.conf',
                                array('@INCLUDE@'=>$pearPhpDir, '@WEB_DIR@'=>$wwwDir,
                                '@PORT@'=>$wwwPort, '@IP@'=>$wwwIp));
                
                $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR
                               .'handler.php.tpl', $wwwDir. DIRECTORY_SEPARATOR .'handler.php',
                               array('@STATUSDIR@' => $statusDir, '@ETC@'=>$etcDir));
                $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR
                               . 'init.d' . DIRECTORY_SEPARATOR . 'xinc.bat',
                                $initDir . DIRECTORY_SEPARATOR . 'xinc.bat',
                                array('@ETC@' => $etcDir, '@LOG@' => $logDir, '@STATUSDIR@' => $statusDir,
                                      '@DATADIR@' => $dataDir));
        
                //$this->_createWindowsService();
                
                
                $this->_ui->outputData('Xinc installation complete.');
                $this->_ui->outputData("- Please include $etcDir" . DIRECTORY_SEPARATOR
                                      . "www.conf in your apache virtual hosts.");
                $this->_ui->outputData("- Please enable mod-rewrite.");
                $this->_ui->outputData("- To add projects to Xinc, copy the project xml to $etcConfDir");
                $this->_ui->outputData("- To start xinc execute: $initDir\xinc.bat");
                $this->_ui->outputData("- To install as a service google for srvany.exe and instsrv.exe");
                break;
                case '_undoOnError' :
                       
                    break;
        }
        
    }

    private function _createWindowsService()
    {
        $binDir = PEAR_Config::singleton()->get('bin_dir');
        
        exec('"' . $binDir . DIRECTORY_SEPARATOR . 'instsrv.exe" xinc "'
            . $binDir . DIRECTORY_SEPARATOR . 'srvany.exe"', $out, $res1);
            $this->_ui->outputData('"' . $binDir . DIRECTORY_SEPARATOR . 'instsrv.exe" xinc "'
            . $binDir . DIRECTORY_SEPARATOR . 'srvany.exe"');
        if ($res1!=0) {
            $this->_ui->outputData('Could not install windows service');
            return;
            //$this->_failedInstall();
        }
        exec('reg add "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Xinc\Parameters" /f', $out, $res2);
        if ($res2!=0) {

            exec('"' . $binDir . DIRECTORY_SEPARATOR . 'instsrv.exe" xinc remove');
            echo('"' . $binDir . DIRECTORY_SEPARATOR . 'instsrv.exe" xinc remove');
            $this->_ui->outputData('Could not install windows service');
            //$this->_failedInstall();
        }
        exec('reg add HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Xinc\Parameters'
            .' /v Application /t REG_SZ /d "'
            . $binDir . DIRECTORY_SEPARATOR.'xinc.bat" /f', $out, $res3);
        if ($res3!=0) {
 
            exec($binDir . DIRECTORY_SEPARATOR . 'instsrv.exe xinc remove');
            exec('reg delete HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Xinc\Parameters /f');
            echo($binDir . DIRECTORY_SEPARATOR . 'instsrv.exe xinc remove');
            echo('reg delete HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Xinc\Parameters /f');
            $this->_ui->outputData('Could not install windows service');
            //$this->_failedInstall();
        }
        
    }
    
    private function _execCat($src, $dest,  $replacements)
    {
        $contents = file_get_contents($src);
        $contents = str_replace(array_keys($replacements), array_values($replacements), $contents);
        file_put_contents($dest, $contents);
    }
    private function _failedInstall()
    {
        
        foreach ($this->_undoTasks as $command) {
            $message=" -> $command:";
            exec($command, $out, $res);
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
}