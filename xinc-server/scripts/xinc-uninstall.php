<?php
require_once 'PEAR/Config.php';

$uninstallFileFile = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'xinc.uninstall.files';
$uninstallDirFile = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'xinc.uninstall.dirs';

if (DIRECTORY_SEPARATOR != '/') {
    exec('winserv uninstall Xinc', $out, $res);
    if ($res==0) {
        echo "[OK] Uninstalled Xinc service\n";
    } else {
        echo "[NOK] Could not uninstall Xinc service\n";
    }
}

if (file_exists($uninstallFileFile)) {

    $files = file($uninstallFileFile);
    foreach ($files as $file) {
    	$file = trim($file);
        echo 'Uninstalling file "' . $file . '":';
        if (is_dir($file)) {
        	exec('rm -Rf '.$file,$out,$res1);
        	$res = $res1 == 0 ? true:false;
        } else {
        	if (file_exists($file)) {
        	$res = unlink($file);
        	} else {
        	  echo 'Does not exists:';
        	  $res=false;
        	}
        }
        echo $res?'OK':'NOK';
        echo "\n";
    }

}
if (file_exists($uninstallDirFile)) {

    $dirs = file($uninstallDirFile);
    foreach ($dirs as $dir) {
    	$dir = trim($dir);
        echo 'Uninstalling directory "' . $dir . '":';
        if (DIRECTORY_SEPARATOR == '/') {
            exec('rm -Rf '.$dir,$out,$res1);
        } else {
            exec('rmdir /S /Q "' . $dir . '"', $out, $res1);
        }
        $res = $res1 == 0 ? true:false;
        echo $res?'OK':'NOK';
        echo "\n";
    }

}
echo 'Uninstall complete.'."\n";
?>