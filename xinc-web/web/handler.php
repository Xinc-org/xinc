<?php

global $_NO_RUN,$_NO_RUN_REASON;;

require_once 'Xinc/Ini.php';
require_once 'Xinc/Gui/Handler.php';

if (isset($_NO_RUN) && $_NO_RUN === true) {
    switch ($_NO_RUN_REASON) {
        case 1:
            echo "<h4>Xinc/Ini.php could not be loaded. Please make sure the PEAR directory is in the include path</h4>";
            break;
        case 2:
            echo "<h4>you need to enable the mod_rewrite module</h4>";
            break;
        default:
            echo "<h4>Configuration problems prevent Xinc-GUI from working properly</h4>";
    }
} else {
    /*
     * 
     * get environment variables or read config.xml
     */
    $handler=new Xinc_Gui_Handler(Xinc_Ini::getInstance()->get('etc','xinc') . DIRECTORY_SEPARATOR . "system.xml",Xinc_Ini::getInstance()->get('status_dir','xinc'));
    
    $handler->view();

}
