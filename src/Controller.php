<?php

namespace Architekt;

use Architekt\Auth\Access\ClassAttributesParser;
use Architekt\Auth\Access\ControllerParser;
use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;

class Controller extends DBEntity
{

    use DBEntityCache;

    protected static ?string $_table = 'controller';

    public function application(): Application
    {
        return Application::fromCache($this->_get('application_id'));
    }

    public function plugin(): Plugin
    {
        return Plugin::fromCache($this->_get('plugin_id'));
    }

    public static function byNameSystem(Plugin $plugin, string $nameSystem): ?static
    {
        $that = new static;
        $that->_search()
            ->and($that, $plugin)
            ->and($that, 'name_system', $nameSystem)
            ->limit();

        if ($that->_next()) {
            return $that;
        }

        return null;
    }

    public static function byApplicationAndNameSystem(Application $application, string $nameSystem): ?static
    {
        $that = new static;
        $that->_search()
            ->and($that, $application)
            ->and($that, 'name_system', $nameSystem)
            ->limit();

        if ($that->_next()) {
            return $that;
        }

        return null;
    }

    public function parse(): ClassAttributesParser
    {
        return ControllerParser::attributes($this);
    }
}