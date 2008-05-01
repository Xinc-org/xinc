<?php
if (isset($_SERVER['WINDIR'])){
    /**
     * some windows allinone packages like XAMPP are missing the trailing \ in
     * the PHP includepath, adding it here
     */
    $include_path = ini_get('include_path');
    $parts = split(';', $include_path);
    $newParts = array();
    foreach ($parts as $part) {
        $newParts[] = realpath($part);
    }

    ini_set('include_path',join(';', $newParts));
}
ini_set('display_errors', 'off');