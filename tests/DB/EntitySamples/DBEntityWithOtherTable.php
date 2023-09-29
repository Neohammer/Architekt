<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use Architekt\DB\DBEntity;
use Architekt\DB\DBRecordRow;

class DBEntityWithOtherTable extends DBEntity
{
    protected static ?string $_table = 'table_other_entity';
    protected static ?string $_table_prefix = '';

    use DBEntityHelperTrait;

    public static function _test_createTable(): void
    {
        (new self)->_connexion()->datatableCreate(
            (new DBDatatable(self::$_table))
                ->addColumn(DBDatatableColumn::buildAutoincrement('uid'))
                ->addColumn(DBDatatableColumn::buildString('name', 100)->setDefault(null))
                ->addColumn(DBDatatableColumn::buildBoolean('active')->setDefault(null))
        );
    }

    public static function _test_createRow(
        ?string $name = null,
        ?string $active = null
    ): int
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
        return new self(
            self::_test_createRow(
                'test1',
                '1'
            )
        );
    }

    public function _test_setDatabase(string $database): self
    {
        $this->_database = $database;
        return $this;
    }

    public function _test_setTable(string $table): self
    {
        $this->_table = $table;
        return $this;
    }

    public function _test_setTablePrefix(string $prefix): self
    {
        $this->_table_prefix = $prefix;
        return $this;
    }

    public function _test_setLabel(string $label): self
    {
        $this->_labelField = $label;
        return $this;
    }
}
