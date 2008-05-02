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
    }
    
    protected function _deleteFile($file, $extra='')
    {
        return unlink($file);
    }
}