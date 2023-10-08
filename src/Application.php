<?php

namespace Architekt;

use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;
use Architekt\Http\Controller;
use Architekt\Utility\ApplicationSettings;
use Architekt\Utility\Settings;

if (!defined('ARCHITEKT_DATATABLE_PREFIX')) {
    define('ARCHITEKT_DATATABLE_PREFIX', 'at_');
}

class Application extends DBEntity
{
    use DBEntityCache;

    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'application';

    public static Configurator $configurator;
    public static Application $application;

    static public function start(Configurator $configurator, bool $autoInit = true)
    {
        self::$configurator = $configurator;
        self::$application = static::byNameSystem($configurator->get('name'));

        if ($autoInit) {
            Controller::init();
        }
    }

    public static function get(): Application
    {
        return self::$application;
    }

    public static function byNameSystem(string $nameSystem): ?static
    {
        $that = new static;
        $that->_search()->and($that, 'name_system', $nameSystem);

        if($that->_next()){
            return $that;
        }

        return null;
    }

    public static function byApplicationUserName(string $applicationUserName): ?static
    {
        $that = new static;
        $that->_search();

        while($that->_next()){
            if(Settings::byApplication($that)->applicationUser() === $applicationUserName){
                return $that;
            }
        }

        return null;
    }

    private static array $urls = [
       // 'admin' => URL_ADMIN,
       // 'game' => URL_GAME,
    ];
    private static array $paths = [
       // 'admin' => URL_ADMIN,
       // 'game' => URL_GAME,
    ];

    public static function list(): array
    {
        return [
            'admin' => 'Administration',
            'game'  => 'Jeu',
            'architekt' => 'Architekt'
        ];
    }

    public static function url(?string $applicationNameSystem = null): string
    {
        return self::$urls[$applicationNameSystem ?? self::name()];
    }

    public static function name(): string
    {
        return Application::$configurator->get('name');
    }

    public static function controllerNamespace(\Architekt\Controller $controller): string
    {
        return sprintf('Website\%s', ucfirst($controller->application()->_get('name_system')));
    }

    public static function path(?string $applicationNameSystem = null): string
    {
        return PATH_PROJECT . DIRECTORY_SEPARATOR . '_' . strtolower($applicationNameSystem ?? self::name());
    }

    public static function controllerPath(?string $app = null): string
    {
        return self::path($app) . DIRECTORY_SEPARATOR . 'controllers';
    }

    public static function controllerFile(string $controllerNameSystem, ?string $applicationNameSystem = null): string
    {
        return sprintf(
            '%s'.DIRECTORY_SEPARATOR.'%sController.php',
            self::controllerPath($applicationNameSystem),
            ucfirst($controllerNameSystem)
        );
    }

    public function settings(): ApplicationSettings
    {
        return Settings::byApplication($this);
    }
}