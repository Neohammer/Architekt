<?php

function _architecktAutoloader($class)
{
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $file = PATH_CLASSES . DIRECTORY_SEPARATOR . $class . '.php';
    if(str_starts_with($class, 'Website')){
        $classParts = explode(DIRECTORY_SEPARATOR, $class);
        unset($classParts[0],$classParts[1]);
        $file = PATH_CONTROLLERS . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $classParts) .'.php';
    }

    if (file_exists($file)) {
        require($file);
        return;
    }
}

spl_autoload_register('_architecktAutoloader');