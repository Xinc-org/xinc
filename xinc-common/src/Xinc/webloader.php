<?php

ini_set('display_errors', 'off');

global $_NO_RUN,$_NO_RUN_REASON;

$res = @include_once('Xinc/Ini.php');
if ($res) {
    $_NO_RUN = false;
} else {
    
    $_NO_RUN = true;
    $_NO_RUN_REASON = 1;
}