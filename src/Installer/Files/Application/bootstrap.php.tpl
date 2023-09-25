<?php

use Architekt\Application;
use Architekt\Configurator;

session_start();

require(__DIR__ . '/../bootstrap.php');
require(__DIR__ . '/constants.php');

Application::start(
    (new Configurator())
        ->setArray([
            'path' => __DIR__,
            'name' => '{$APPLICATION_NAME}',
            'medias' => URL_{$APPLICATION_CDN_CODE_UPPER},
        ])
);