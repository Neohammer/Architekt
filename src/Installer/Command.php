<?php

namespace Architekt\Installer;

class Command
{
    public static function install(string $path): void
    {
        Architekt::init($path)->install();
    }

    public static function sql(string $path): void
    {
        Architekt::init($path)->sql();
    }
}