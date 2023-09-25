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

    private static array $urls = [
        'admin' => URL_ADMIN,
        'game' => URL_GAME,
    ];
    private static array $paths = [
        'admin' => URL_ADMIN,
        'game' => URL_GAME,
    ];

    public static function list(): array
    {
        return [
            'admin' => 'Administration',
            'game'  => 'Jeu',
            'architekt' => 'Architekt'
        ];
    }

    public static function url(?string $app = null): string
    {
        return self::$urls[$app ?? self::name()];
    }

    public static function name(): string
    {
        return Application::$configurator->get('name');
    }

    public static function controllerNamespace(?string $app = null): string
    {
        return sprintf('\Website\%s', ucfirst($app ?? self::name()));
    }

    public static function path(?string $app = null): string
    {
        return PATH_PROJECT . DIRECTORY_SEPARATOR . '_' . strtolower($app ?? self::name());
    }

    public static function controllerPath(?string $app = null): string
    {
        return self::path($app) . DIRECTORY_SEPARATOR . 'controllers';
    }

    public static function controllerFile(string $module, ?string $app = null): string
    {
        return sprintf(
            '%s'.DIRECTORY_SEPARATOR.'%sController.php',
            self::controllerPath($app),
            ucfirst($module)
        );
    }
}