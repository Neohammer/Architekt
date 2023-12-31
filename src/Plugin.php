<?php

namespace Architekt;

use Architekt\Auth\Access\ClassAttributesParser;
use Architekt\Auth\Access\ControllerParser;
use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;

if (!defined('ARCHITEKT_DATATABLE_PREFIX')) {
    define('ARCHITEKT_DATATABLE_PREFIX', 'at_');
}

class Plugin extends DBEntity
{
    use DBEntityCache;

    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'plugin';

    public static function byNameSystem(Application $application,string $nameSystem): ?static
    {
        $that = new static;
        $that->_search()
            ->and($that, $application)
            ->and($that, 'name_system', $nameSystem)
        ;

        if($that->_next()){
            return $that;
        }

        return null;
    }

    public function labelOption(): string
    {
        return sprintf(
            '%s > %s',
            $this->_get('app'),
            $this->_get('name')
        );
    }
}