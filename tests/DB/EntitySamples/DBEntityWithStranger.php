<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Database;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use Architekt\DB\DBEntity;
use Architekt\DB\DBRecordRow;

class DBEntityWithStranger extends DBEntity
{
    protected static ?string $_table = 'sql_test_entity_stranger';
    use DBEntityHelperTrait;

    public static function _test_createTable(): void
    {
        (new self)->_connexion()->datatableCreate(
            (new DBDatatable(self::$_table))
                ->addColumn(DBDatatableColumn::buildAutoincrement())
                ->addColumn(DBDatatableColumn::buildString('name', 100)->setDefault(null))
                ->addColumn(DBDatatableColumn::buildBoolean('active')->setDefault(null))
                ->addColumn(DBDatatableColumn::buildInt('sql_test_entity_id',3)->setDefault(null))
        );
    }

    public static function _test_createRow(
        ?string         $name = null,
        null|int|string $active = null,
        ?DBEntitySimple $entitySimple = null
    ): string|int
    {
        self::_test_createTable();

        (new self)->_connexion()->recordInsert(
            (new DBRecordRow(self::$_table))
                ->set('name', $name)
                ->set('active', $active)
                ->set('sql_test_entity_id', $entitySimple?->_primary())
        );

        return (new self)->_connexion()->recordInsertLast();
    }

    public static function _test_createSample(): self
    {
        $entitySimple = DBEntitySimple::_test_createSample();
        return new self(
            self::_test_createRow(
                'teststranger1',
                '2',
                $entitySimple
            )
        );
    }
}
