<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLRecordDelete extends MySQLTools implements DBQueryBuilderInterface
{
    private array $filters;
    private array $params;

    use MySQLRecordFilterTrait;

    public function __construct(private DBRecordRow $recordRow)
    {
        $this->filters = [];
        $this->params = [];

        $this->buildFilters($this->recordRow);
    }

    public function query(): Query
    {
        return new Query(
            sprintf(
                'DELETE FROM %s%s',
                self::quote($this->recordRow->datatable()),
                join(' ', $this->filters)
            ),
            $this->params
        );
    }

}