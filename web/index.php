<?php
require_once 'Xinc/Gui/Handler.php';

$handler=new Xinc_Gui_Handler("/etc/xinc/plugins.xml","/var/log/xinc/");

$handler->view();
?>