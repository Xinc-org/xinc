<?php

require_once 'Xinc/Ini.php';
require_once 'Xinc/Gui/Handler.php';

/*
 * get environment variables or read config.xml
 */
$handler = new Xinc_Gui_Handler(
    Xinc_Ini::getInstance()->get('etc', 'xinc') . DIRECTORY_SEPARATOR . 'system.xml',
    Xinc_Ini::getInstance()->get('status_dir', 'xinc')
);
$handler->view();
