<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\Interfaces\DBDatatableInterface;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLDatatableExists extends MySQLTools implements DBQueryBuilderInterface
{
    private DBDatatableInterface $datatable;

    public function __construct(DBDatatableInterface $DBDatatable)
    {
        $this->datatable = $DBDatatable;
    }

    public function query(): Query
    {
        return (new MySQLDatatableSearch())
            ->filter($this->datatable)
            ->query();
    }
}