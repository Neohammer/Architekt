<?php

namespace Architekt;

use Architekt\Http\Controller;

class Application
{
    public static Configurator $configurator;

    static public function start(Configurator $configurator, bool $autoInit = true)
    {
        self::$configurator = $configurator;
        if ($autoInit) {
            Controller::init();
        }
    }
}