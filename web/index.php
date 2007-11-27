<?php

require_once 'Xinc/Gui/Handler.php';

/*
 * 
 * get environment variables or read config.xml
 */
$handler=new Xinc_Gui_Handler("/etc/xinc/system.xml","/var/xinc/status");

$handler->view();
?>