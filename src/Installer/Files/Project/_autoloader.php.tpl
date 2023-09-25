<?php

function _architecktAutoloader($class)
{
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = PATH_CLASSES . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require($file);
        return;
    }
}

spl_autoload_register('_architecktAutoloader');