<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use Architekt\DB\DBEntity;
use Architekt\DB\DBRecordRow;

class DBEntitySimple extends DBEntity
{
    protected static ?string $_table = 'sql_test_entity';
    protected static ?string $_table_prefix = '';

    use DBEntityHelperTrait;

    public static function _test_createTable(): void
    {
        (new self)->_connexion()->datatableCreate(
            (new DBDatatable(self::$_table))
                ->addColumn(DBDatatableColumn::buildAutoincrement())
                ->addColumn(DBDatatableColumn::buildString('name', 100)->setDefault(null))
                ->addColumn(DBDatatableColumn::buildBoolean('active')->setDefault(null))
        );
    }

    public static function _test_createRow(
        ?string         $name = null,
        null|int|string $active = null
    ): string|int
    {
        self::_test_createTable();

        (new self)->_connexion()->recordInsert(
            (new DBRecordRow(self::$_table))
                ->set('name', $name)
                ->set('active', $active)
        );

        return (new self)->_connexion()->recordInsertLast();
    }

    public static function _test_createSample(): self
    {
        return new static(
            self::_test_createRow(
                'test1',
                '1'
            )
        );
    }

    public static function _test_createSample2(): self
    {
        return new static(
            static::_test_createRow(
                'test2',
                '0'
            )
        );
    }
}
