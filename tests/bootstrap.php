<?php

use Architekt\Http\Environment;

$_SERVER['SERVER_NAME'] = 'test.mon-domaine.fr';

//require(__DIR__ . '/../constants.php');
//require(__DIR__ . '/../classes/_autoloader.php');


function _phpunitAutoloader($class)
{
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/../' . $class . '.php';
    if (file_exists($file)) {
        require($file);
    }
}

spl_autoload_register('_phpunitAutoloader');

Environment::add('test', [
    'testServer' => $_SERVER['SERVER_NAME']
]);

Environment::requireFile(__DIR__ . '/config');