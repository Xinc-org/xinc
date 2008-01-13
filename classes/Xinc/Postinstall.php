<?php
class Xinc_Postinstall_postinstall
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
    
    public function init(&$config, &$pkg, $lastversion)
    {
        $this->_config = &$config;
        $this->_registry = &$config->getRegistry();
        $this->_ui = &PEAR_Frontend::singleton();
        $this->_pkg = &$pkg;
        $this->lastversion = $lastversion;
        return true;
    }

    public function postProcessPrompts($prompts, $section)
    {
       
        foreach ($prompts as $item) {
            
        }
       
        return $prompts;
    }

    private function _createDir($dirName, $permission)
    {
        if (file_exists($dirName)) {
            if (!is_writeable($dirName)) {
                $this->_ui->outputData($dirName . ' is not writable');
                return $this->_failedInstall();
            }
        } else {
            $parentDir = dirname($dirName);
            if (!is_writable($parentDir)) {
                $this->_ui->outputData($parentDir . ' is not writable');
                return $this->_failedInstall();
            }
            
            $res = mkdir($dirName, $permission, true);
            if (!$res) {
                $this->_ui->outputData('Could not create ' . $dirName);
                return $this->_failedInstall();
            }
            $this->_undoTasks[] = 'rm ' . $dirName . ' -Rf';
        }
        return true;
    }
    
    private function _copyFiles($src, $target, $extra = '')
    {
        exec('cp ' . $src . ' ' . $target . ' ' . $extra, $out, $res);
        if ($res != 0) {
            $this->_ui->outputData('Could not copy "' . $src . '" to: ' . $target);
            return $this->_failedInstall();
        } else {
            $this->_undoTasks[] = 'rm ' . $target . ' -Rf';
            $this->_ui->outputData('Successfully copied ' . $src . '  to: ' . $target);
        }
        return true;
    }
    
    private function _execCmd($cmd)
    {
        exec($cmd, $out, $res);
        return $res;
    }
    
    public function run($answers, $phase)
    {
        
        $pearDataDir = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc';
        $xincPhpDir = PEAR_Config::singleton()->get('php_dir') . DIRECTORY_SEPARATOR . 'Xinc';
        $pearPhpDir = PEAR_Config::singleton()->get('php_dir');
        
        switch($phase) {
            
            case 'daemoninstall':
                $etcDir = $answers['etc_dir'];
                $etcConfDir = $etcDir . DIRECTORY_SEPARATOR . 'conf.d';
                $this->_createDir($etcDir, 0655);
                $this->_createDir($etcConfDir, 0655);
                
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR
                                 . 'xinc' . DIRECTORY_SEPARATOR . '*', $etcDir);
                
                $xincDir = $answers['xinc_dir'];
                $dataDir = $xincDir . DIRECTORY_SEPARATOR . 'projects';
                $statusDir = $xincDir . DIRECTORY_SEPARATOR . 'status';
                
                $this->_createDir($xincDir, 0655);
                $this->_createDir($dataDir, 0655);
                $this->_createDir($statusDir, 0655);
                
                $initDir = $answers['initd_dir'];
                $this->_createDir($initDir, 0655);
                $installExamples = $answers['install_examples'] == 'yes' ? true: false;
                
                if ($installExamples) {
                    $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR 
                                     . 'SimpleProject', $dataDir, '-Rf');
                }
                $logDir = $answers['log_dir'];
                $xinclogDir = $logDir . DIRECTORY_SEPARATOR . 'xinc';
                $this->_createDir($logDir, 0655);
                
                if ($installExamples) {
                    $res = $this->_execCmd('cat '.$dataDir . '/SimpleProject/build.tpl.xml | sed -e "s#@EXAMPLE_DIR@#' 
                                          . $dataDir . '#" > '.$dataDir.'/SimpleProject/build.xml');
                    $res = $this->_execCmd('rm ' . $dataDir . '/SimpleProject/build.tpl.xml');
                    $res = $this->_execCmd('cat '.$dataDir . '/SimpleProject/publish.tpl.xml | sed -e "s#@EXAMPLE_DIR@#'
                                          . $dataDir . '#" > '.$dataDir.'/SimpleProject/publish.xml');
                    $res = $this->_execCmd('rm ' . $dataDir . '/SimpleProject/publish.tpl.xml');
                    $res = $this->_execCmd('cat ' . $pearDataDir
                                          . '/examples/simpleproject.tpl.xml | sed -e "s#@EXAMPLE_DIR@#'
                                          . $dataDir . '#" > '.$etcConfDir.'/simpleproject.xml');
                }
                //exec($pearDataDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear-install.sh');
                $wwwDir = $answers['www_dir'];
                $wwwPort = $answers['www_port'];
                $wwwIp =  $answers['www_ip'];
                $this->_createDir($wwwDir, 0755);
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'web'
                                 . DIRECTORY_SEPARATOR . '.htaccess', $wwwDir);
                $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'web'
                                 . DIRECTORY_SEPARATOR . '*', $wwwDir, '-Rf');
                $this->_execCmd('rm '.$wwwDir.'/www.tpl.conf');
                $this->_execCmd('rm ' . $wwwDir . '/handler.php.tpl');
                
                $this->_execCmd('cat ' . $pearDataDir . DIRECTORY_SEPARATOR . 'web/www.tpl.conf | sed -e "s#@INCLUDE@#'
                               . $pearPhpDir . '#" | sed -e "s#@WEB_DIR@#'.$wwwDir.'#" | sed -e "s#@PORT@#'
                               . $wwwPort . '#" | sed -e "s#@IP@#'.$wwwIp.'#" > '.$etcDir . '/www.conf');
                
                $this->_execCmd('cat ' . $pearDataDir . DIRECTORY_SEPARATOR
                               . 'web/handler.php.tpl | sed -e "s#@STATUSDIR@#'
                               . $statusDir . '#" | sed -e "s#@ETC@#'.$etcDir.'#" > '.$wwwDir.'/handler.php');
                
                $this->_execCmd('cat ' . $pearDataDir . '/etc/init.d/xinc | sed -e "s#@ETC@#' . $etcDir
                               . '#" | sed -e "s#@LOG@#'.$logDir.'#" | sed -e "s#@STATUSDIR@#'. $statusDir
                               .'#" | sed -e "s#@DATADIR@#'.$dataDir.'#" > '.$initDir.'/xinc');
                $this->_execCmd('chmod ugo+x '.$initDir.'/xinc');
                
                $this->_ui->outputData('Xinc installation complete.');
                $this->_ui->outputData("- Please include $etcDir/www.conf in your apache virtual hosts.");
                $this->_ui->outputData("- Please enable mod-rewrite.");
                $this->_ui->outputData("- To add projects to Xinc, copy the project xml to $etcConfDir");
                $this->_ui->outputData("- To start xinc execute: sudo $initDir/xinc start");
                break;
            case '_undoOnError' :
                   
                break;
        }
        
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