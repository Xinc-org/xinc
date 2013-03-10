        $package->addReplacement('xinc.ini.tpl', 'package-info', '@VERSION@', 'version');
        $package->addReplacement('scripts/xinc-uninstall', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('scripts/xinc-uninstall', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('etc/init.d/xinc', 'pear-config', '@BIN_DIR@', 'bin_dir');
        $package->addReplacement('bin/xinc', 'pear-config', '@PHP_BIN@', 'php_bin');
        $package->addReplacement('Xinc/scripts/pear-install.sh', 'pear-config', '@data-dir@','data_dir');
