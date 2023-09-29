<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\DBDatatable;

trait DBEntityHelperTrait
{

    public static function _test_clearTable(): void
    {
        (new self)->_connexion()->datatableEmpty(
            new DBDatatable(self::$_table)
        );
    }

    public static function _test_dropTable(): void
    {
        (new self)->_connexion()->datatableDelete(
            new DBDatatable(self::$_table)
        );
    }
}