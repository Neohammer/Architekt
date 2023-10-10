<?php

namespace Architekt\Installer;

class Command
{
    public static function install(string $path): void
    {
        Architekt::init($path)->install();
    }

    public static function toJson(string $path): void
    {
        Architekt::init($path)->toJson();
    }

    public static function info(string $text)
    {
        echo sprintf('[>] %s'."\n",$text);
    }

    public static function warning(string $text)
    {
        echo sprintf('[?] %s'."\n",$text);
    }

    public static  function error(string $text)
    {
        echo sprintf('[!] %s'."\n",$text);
    }

    public static function addController(
        string $path,
        string $controllerCode,
        string $projectCode,
        string $applicationCode,
        string $pluginName = 'userCustom',
    )
    {
        Architekt::init($path)->installController(
            $controllerCode,
            $projectCode,
            $applicationCode,
            $pluginName,
        );
    }

    public static function addSubController(
        string $path,
        string $controllerCode,
        string $controllerSubCode,
        string $projectCode,
        string $applicationCode,
        string $pluginName = 'userCustom',
    )
    {
        Architekt::init($path)->installSubController(
            $controllerCode,
            $controllerSubCode,
            $projectCode,
            $applicationCode,
            $pluginName,
        );
    }

    public static function updateWebVendors(
        string $path,
        string $projectCode,
        string $applicationCode
    )
    {
        Architekt::init($path)->updateWebVendors($projectCode, $applicationCode);
    }
}