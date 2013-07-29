<?php

global $_NO_RUN,$_NO_RUN_REASON;

require_once 'Xinc/Ini.php';
require_once 'Xinc/Gui/Handler.php';

if (isset($_NO_RUN) && $_NO_RUN === true) {
    switch ($_NO_RUN_REASON) {
        case 1:
            echo 'Xinc/Ini.php could not be loaded. Please make sure the PEAR directory is in the include path';
            break;
        case 2:
            echo 'you need to enable the mod_rewrite module';
            break;
        default:
            echo 'Configuration problems prevent Xinc-GUI from working properly';
    }
} else {
    /*
     * 
     * get environment variables or read config.xml
     */
    $handler = new Xinc_Gui_Handler(
        Xinc_Ini::getInstance()->get('etc', 'xinc') . DIRECTORY_SEPARATOR . 'system.xml',
        Xinc_Ini::getInstance()->get('status_dir', 'xinc')
    );
    $handler->view();
}
