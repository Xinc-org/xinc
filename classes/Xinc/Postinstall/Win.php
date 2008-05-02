<?php
require_once 'Xinc/Postinstall.php';

class Xinc_Postinstall_Win_postinstall extends Xinc_Postinstall
{
    
    public function init(&$config, &$pkg, $lastversion)
    {
        return parent::init($config,$pkg,$lastversion);
    }
    public function run($answers, $phase)
    {
        return parent::run($answers, $phase);
    }
    protected function _createDir($dirName, $permission)
    {
      if($permission==null) $permission=777;
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
            $this->_undoDirs[] = $dirName; //;'rmdir "' . $dirName . '" /s /q';
            $this->_uninstallDirs[] = $dirName;
        }
        return true;
    }
    protected function _deleteDir($dirname, $extra='')
    {
        $out = null;
        $res = null;
        exec('rmdir /S /Q "' . $dirname . '"', $out, $res);
        return $res==0;
    }
    protected function _copyFiles($src, $target, $extra = '')
    {
        $out = null;
        $res = null;
        $files = glob($src);
        
        if (!empty($extra)) {
            foreach ($files as $file) {
                $targetDirName = $target;
                if (is_dir($file) && is_dir($target)) {
                    $targetDirName = $target . DIRECTORY_SEPARATOR . basename($file);
                    if (!file_exists($targetDirName)) {
                        mkdir($targetDirName);
                    }
                    $file = $file . DIRECTORY_SEPARATOR . '*';
                    
                }
                $cmd = 'xcopy /E /Y "' . $file . '" "' . $targetDirName .'"';
                exec($cmd, $out, $res);
                $this->_ui->outputData($cmd, $out, $res);
                $baseFileName = basename($file);
                $targetDir = dirname($target);
                
            }
            
        } else {
            $cmd = 'copy /Y "' . $src . '" "' . $target .'"';
            exec($cmd, $out, $res);
            $this->_ui->outputData($cmd, $out, $res);
        }
        
        
        if ($res != 0) {
            $this->_ui->outputData('Could not copy "' . $src . '" to: ' . $target);
            return $this->_failedInstall();
        } else {
            $srcDir = dirname($src);
            $srcDir = realpath($srcDir);
            foreach ($files as $file) {
                $file = realpath($file);
                if (is_dir($target)) {
                    $targetDir = $target;
                } else {
                    $targetDir = dirname($target);
                }
                $undo = $targetDir . DIRECTORY_SEPARATOR . str_replace($srcDir, '', $file);
                //$undo = $targetDir . DIRECTORY_SEPARATOR . $baseFileName;
                if (is_dir($undo)) {
                    $this->_undoDirs[] = $undo;
                    $this->_uninstallDirs[] = $undo;
                } else {
                    $this->_undoFiles[] = $undo;
                    $this->_uninstallFiles[] = $undo;
                }
            }
            $this->_ui->outputData('Successfully copied ' . $src . '  to: ' . $target);
        }
        return true;
    }
    
    
    

    private function _createWindowsService($binDir, $pearDataDir, $etcDir, $logDir, $statusDir, $dataDir, $initDir)
    {
        $winDir = isset($_SERVER['SystemRoot'])?$_SERVER['SystemRoot']:"C:\\Windows\\";
        $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'winserv.exe',
                     $winDir . DIRECTORY_SEPARATOR . 'winserv.exe');
        $XINC_CONFIG="$etcDir\system.xml";
        $XINC_PROJECTS="$etcDir\conf.d\*.xml";
        $XINC_LOG="$logDir/xinc.log";
        $XINC_STATUS=$statusDir;
        $phpBin = $this->_config->get('php_bin');
        $XINC_DATADIR=$dataDir;
        exec('winserv install Xinc -ipcmethod blind ' . $phpBin . "$binDir\xinc.php -f $XINC_CONFIG -p $XINC_DATADIR -s $XINC_STATUS -w $XINC_DATADIR -l $XINC_LOG $XINC_PROJECTS", $out, $res);
        if ($res!=0) {
            $this->_ui->outputData('Could not install windows service');
        } else {
            $this->_ui->outputData('Successfully installed windows service');
        }
    }
    
    
    protected function _platformSpecificInstall($etcDir, $logDir, $statusDir, $dataDir, $initDir)
    {
        $pearDataDir = $this->pearDataDir;
        $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR
                       . 'init.d' . DIRECTORY_SEPARATOR . 'xinc.bat',
                        $initDir . DIRECTORY_SEPARATOR . 'xinc.bat',
                        array('@ETC@' => $etcDir, '@LOG@' => $logDir, '@STATUSDIR@' => $statusDir,
                              '@DATADIR@' => $dataDir));
        $this->_undoFiles[] = $initDir . DIRECTORY_SEPARATOR . 'xinc.bat';
        $this->_uninstallFiles[] = $initDir . DIRECTORY_SEPARATOR . 'xinc.bat';
        $binDir = $this->_config->get('bin_dir');
        $this->_copyFiles($pearDataDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'xinc-uninstall.bat',
                     $binDir . DIRECTORY_SEPARATOR . 'xinc-uninstall.bat');
        $this->_createWindowsService($binDir, $pearDataDir, $etcDir, $logDir, $statusDir, $dataDir, $initDir);
    }
    
    protected function _deleteFile($file, $extra='')
    {
        return unlink($file);
    }
}