#!/usr/bin/php
<?php

require_once '../../../xinc-composer/vendor/autoload.php';
require_once '../../xinc-common/src/Xinc/Logger.php';
require_once 'Xinc/Server/Xinc.php';

$instance = Xinc\Server\Xinc::getInstance();

if ($instance) {
    $instance->run();
} else {
    echo 'Couldn\'t create instance, hopefully you got some error messages on console or in the log file.';
}
