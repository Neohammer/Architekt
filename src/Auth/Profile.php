<?php

namespace Architekt\Auth;

use Architekt\Application;
use Architekt\Controller;
use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;
use Architekt\Utility\ProfileSettings;
use Architekt\Utility\Settings;

if(!defined('ARCHITEKT_DATATABLE_PREFIX')){
    define('ARCHITEKT_DATATABLE_PREFIX' , '');
}

class Profile extends DBEntity
{
    use DBEntityCache;

    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'profile';

    public function labelOption(): string
    {
        return sprintf('%s > %s', $this->application()->label(), parent::labelOption());
    }

    public function allowController(Controller $controller, ?string $access = null): bool
    {
        return Access::has(
            $this,
            $controller,
            $access
        );
    }

    public function allow(string $controllerNameSystem, ?string $access = null): bool
    {
        return Access::has(
            $this,
            Controller::byApplicationAndNameSystem(Application::get(), $controllerNameSystem),
            $access
        );
    }

    public static function default(Application $application, bool $user = false): ?static
    {
        $that = new static;
        $that->_search()
            ->and($that, $application)
            ->and($that, 'default', 1)
            ->and($that, 'user' , $user?'1':'0');

        if ($that->_next()) {
            return $that;
        }

        return null;
    }

    public function application(): Application
    {
        return Application::fromCache($this->_get('application_id'));
    }

    public function settings(): ProfileSettings
    {
        return Settings::byProfile($this);
    }

}