<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLRecordUpdate extends MySQLTools implements DBQueryBuilderInterface
{
    private DBRecordRow $DBDatatableRow;
    private array $fields;
    private array $filters;
    private array $params;
    private array $values;

    use MySQLRecordFilterTrait;

    public function __construct(DBRecordRow $DBDatatableRow)
    {
        $this->DBDatatableRow = $DBDatatableRow;
        $this->fields = [];
        $this->filters = [];
        $this->params = [];
        $this->values = [];

        $this->buildFields();
        $this->buildFilters($this->DBDatatableRow);
    }

    public function query(): Query
    {
        return new Query(
            sprintf(
                'UPDATE %s SET %s%s',
                self::quote($this->DBDatatableRow->datatable()),
                join(', ', $this->fields),
                join(' ', $this->filters)
            ),
            $this->params
        );
    }

    private function buildFields()
    {
        foreach ($this->DBDatatableRow->values() as $field => $value) {
            if ($value !== null) {
                $this->params[self::prepareFormat($field)] = $value;
            }
            $this->fields[] = sprintf(
                '%s=%s',
                self::quote($field),
                self::prepareFormat($field)
            );
        }
    }
}