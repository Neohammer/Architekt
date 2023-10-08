<?php

namespace Architekt;

use Architekt\DB\DBEntity;

if (!defined('ARCHITEKT_DATATABLE_PREFIX')) {
    define('ARCHITEKT_DATATABLE_PREFIX', '');
}

class Project extends DBEntity
{
    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'project';

    public static function byNameSystem(string $nameSystem): ?static
    {
        $that = new static;
        $that->_search()->and($that, 'name_system', $nameSystem);

        if($that->_next()){
            return $that;
        }

        return null;
    }
}