        // the hard-coded stuff
        $package->setPackage('Xinc');

        // "package" dependencies
        $package->addExtensionDep('required', 'xsl');
        $package->addExtensionDep('required', 'xml');

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

