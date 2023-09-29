<?php

use Architekt\Application;
use Architekt\Configurator;

session_start();

require(dirname(__DIR__) . '/bootstrap.php');
require(__DIR__ . '/constants.php');

Application::start(
    (new Configurator())
        ->setArray([
            'path' => __DIR__,
            'name' => '{$APPLICATION_NAME}',
            'medias' => {if $APPLICATION_CDN}URL_{$APPLICATION_CDN_CODE_UPPER}{else}''{/if},
        ])
);