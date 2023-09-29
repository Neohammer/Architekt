<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\Interfaces\DBDatatableInterface;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLDatatableDelete extends MySQLTools implements DBQueryBuilderInterface
{
    private DBDatatableInterface $datatable;

    public function __construct(DBDatatableInterface $DBDatatable)
    {
        $this->datatable = $DBDatatable;
    }

    public function query(): Query
    {
        return new Query(
            sprintf(
                'DROP TABLE IF EXISTS %s',
                self::quote($this->datatable->name())
            )
        );
    }
}