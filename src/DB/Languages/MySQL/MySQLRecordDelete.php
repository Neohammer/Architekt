<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLRecordDelete extends MySQLTools implements DBQueryBuilderInterface
{
    private DBRecordRow $DBDatatableRow;
    private array $filters;
    private array $params;

    use MySQLRecordFilterTrait;

    public function __construct(DBRecordRow $DBDatatableRow)
    {
        $this->DBDatatableRow = $DBDatatableRow;
        $this->filters = [];
        $this->params = [];

        $this->buildFilters($this->DBDatatableRow);
    }

    public function query(): Query
    {
        return new Query(
            sprintf(
                'DELETE FROM %s%s',
                self::quote($this->DBDatatableRow->datatable()),
                join(' ', $this->filters)
            ),
            $this->params
        );
    }

}