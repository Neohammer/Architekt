<?php

namespace Architekt;

use Architekt\Http\Controller;

class Application
{
    public static Configurator $configurator;

    static public function start(Configurator $configurator)
    {
        self::$configurator = $configurator;
        Controller::init();
    }
}