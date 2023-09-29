<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\Interfaces\DBDatatableInterface;
use Architekt\DB\Interfaces\DBDatatableSearchInterface;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLDatatableSearch extends MySQLTools implements DBDatatableSearchInterface, DBQueryBuilderInterface
{
    private ?DBDatatableInterface $datatable;

    public function __construct()
    {
        $this->datatable = null;
    }

    public function filter(DBDatatableInterface $DBDatatable): static
    {
        $this->datatable = $DBDatatable;

        return $this;
    }

    public function query(): Query
    {
        $filter = '';
        $params = [];
        if ($this->datatable) {
            $filter = ' LIKE :datatableName';
            $params = [
                ':datatableName' => $this->datatable->name()
            ];
        }

        return new Query(
            sprintf(
                'SHOW TABLES%s',
                $filter
            ),
            $params
        );
    }
}