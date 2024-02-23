<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLRecordInsert extends MySQLTools implements DBQueryBuilderInterface
{
    private DBRecordRow $DBDatatableRow;
    private array $fields;
    private array $params;
    private array $values;

    public function __construct(DBRecordRow $DBDatatableRow)
    {
        $this->DBDatatableRow = $DBDatatableRow;
        $this->fields = [];
        $this->params = [];
        $this->values = [];

        $this->buildFields();
    }

    public function query(): Query
    {
        return new Query(
            sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                self::quote($this->DBDatatableRow->datatable()),
                join(', ', $this->fields),
                join(', ', $this->values)
            ),
            $this->params
        );
    }

    private function buildFields()
    {
        foreach ($this->DBDatatableRow->values() as $field => $value) {
            $this->fields[] = self::quote($field);
            if ($value === null) {
                $this->values[] = 'NULL';
            } else {
                $this->values[] = ($key = self::prepareFormat($field));
                $this->params[$key] = $value;
            }
        }
    }
}