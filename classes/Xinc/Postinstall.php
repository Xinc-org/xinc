<?php
require_once 'Xinc/PostinstallBase.php';

class Xinc_Postinstall_postinstall extends Xinc_PostinstallBase
{

    protected function _createDir($dirName, $permission)
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
            $this->_uninstallDirs[] = $dirName;
            $this->_undoDirs[] = $dirName;
        }
        return true;
    }
    protected function _deleteDir($dirname, $extra='')
    {
        $out = null;
        $res = null;
        exec('rm -Rf "' . $dirname . '"', $out, $res);
        return $res==0;
    }
    protected function _copyFiles($src, $target, $extra = '')
    {
        $files = glob($src);
        exec('cp ' . $extra . ' ' . $src . ' ' . $target, $out, $res);
        if ($res != 0) {
            $this->_ui->outputData('Could not copy "' . $src . '" to: ' . $target);
            return $this->_failedInstall();
        } else {
            foreach ($files as $file) {
                $baseFileName = basename($file);
                $this->_undoFiles[] = $target . DIRECTORY_SEPARATOR . $baseFileName;
                $this->_uninstallFiles[] = $target . DIRECTORY_SEPARATOR . $baseFileName;
            }
            $this->_ui->outputData('Successfully copied ' . $src . '  to: ' . $target);
        }
        return true;
    }
    
    protected function _deleteFile($file, $extra='')
    {
        $out=null;
        $res=null;
        exec('rm ' . $extra . ' ' . $file, $out, $res);
        return $res==0;
    }
    
    protected function _platformSpecificInstall($etcDir, $logDir, $statusDir, $dataDir, $initDir)
    {
        $pearDataDir = $this->pearDataDir;
        $this->_execCmd('cat ' . $pearDataDir . '/etc/init.d/xinc | sed -e "s#@ETC@#' . $etcDir
                               . '#" | sed -e "s#@LOG@#'.$logDir.'#" | sed -e "s#@STATUSDIR@#'. $statusDir
                               .'#" | sed -e "s#@DATADIR@#'.$dataDir.'#" > '.$initDir.'/xinc');
                $this->_execCat($pearDataDir . DIRECTORY_SEPARATOR . 'etc'. DIRECTORY_SEPARATOR . 'init.d'.DIRECTORY_SEPARATOR.'xinc',
                                $initDir.DIRECTORY_SEPARATOR.'xinc',
                                array('@ETC@'=>$etcDir, '@LOG@'=>$logDir,
                                '@STATUSDIR@'=>$statusDir, '@DATADIR@'=>$dataDir));
                                
                $this->_execCmd('chmod ugo+x '.$initDir.'/xinc');
                
                $this->_undoFiles[] = $initDir . '/xinc';
                $this->_uninstallFiles[] = $initDir . '/xinc';
    }

}