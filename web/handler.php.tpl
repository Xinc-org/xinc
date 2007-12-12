<?php

require_once 'Xinc/Gui/Handler.php';

/*
 * 
 * get environment variables or read config.xml
 */
$handler=new Xinc_Gui_Handler("@ETC@" . DIRECTORY_SEPARATOR . "system.xml","@STATUSDIR@");

$handler->view();


?>