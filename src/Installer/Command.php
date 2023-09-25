<?php

namespace Architekt\Installer;

class Command
{
    public static function install(string $path): void
    {
        Architekt::init($path)->install();
    }
}