<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\DBDatatable;
use Architekt\DB\DBRecordColumn;
use Architekt\DB\DBRecordRow;

abstract class MySQLTools
{
    final protected static function quote(DBDatatable|DBRecordColumn|DBRecordRow|array|string $parameter): array|string
    {
        if(is_array($parameter)){
            foreach($parameter as $k=>$v){
                $parameter[$k] = self::quote($v);
            }

            return $parameter;
        }

        return sprintf('`%s`', is_object($parameter) ? $parameter->name() : $parameter);
    }

    final protected static function prepareFormat(array|string $parameter, bool $encode = true): array|string
    {
        if (is_array($parameter)) {
            foreach ($parameter as $k => $v) {
                $parameter[$k] = self::prepareFormat($v);
            }

            return $parameter;
        }

        return sprintf(':%s', $encode ? uniqid($parameter) : $parameter);
    }
}