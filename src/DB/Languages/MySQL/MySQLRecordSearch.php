<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBRecordColumn;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Interfaces\DBRecordSearchInterface;

class MySQLRecordSearch extends MySQLTools implements DBRecordSearchInterface
{
    use MySQLRecordFilterTrait;

    /** @var DBRecordColumn[] $select */
    private array $select;

    /** @var DBDatatable[] $datatables */
    private array $datatables;

    /** @var mixed[] $datatablesLeftFilters */
    private array $datatablesLeftFilters;

    /** @var mixed[] $datatablesInnerFilters */
    private array $datatablesInnerFilters;

    /** @var DBRecordRow[] $filterRows */
    private array $filterRows;

    private array $filters;

    private array $leftFilters;
    private array $innerFilters;

    private array $params;

    private array $limit;

    /** @var DBRecordColumn[] $orders */
    private array $orders;

    public function __construct()
    {
        $this->select = [];
        $this->datatables = [];
        $this->datatablesLeftFilters = [];
        $this->datatablesInnerFilters = [];
        $this->filterRows = [];
        $this->filters = [];
        $this->leftFilters = [];
        $this->innerFilters = [];
        $this->params = [];
        $this->limit = [];
        $this->orders = [];
    }

    public function query(): Query
    {

        $this->filters = [];
        $this->params = [];
        $this->buildFilters($this->filterRows, $this->requireDatatable());

        $command = sprintf(
            'SELECT %s FROM %s',
            $this->buildSelect(),
            $this->buildTables(),
        );

        if ($this->filters) {
            $command .= implode(' ', $this->filters);
        }

        if ($this->orders) {
            $command .= $this->buildOrders();
        }

        if ($this->limit) {
            $command .= $this->buildLimit();
        }

        return new Query(
            $command,
            $this->params
        );
    }

    private function requireDatatable(): bool
    {
        return count($this->datatables) > 1;
    }

    private function buildSelect(): string
    {
        $select = [];

        if ($this->select) {
            if ($this->requireDatatable()) {
                foreach ($this->select as $item) {
                    $select[] = sprintf(
                        '%s.%s%s',
                        self::quote($item->datatable()),
                        $item->name() === '*' ? '*' : self::quote($item->name()),
                        $item->alias() ? sprintf(' AS "%s"', $item->alias()) : ''
                    );
                }
            } else {
                $select = $this->select === '*' ? '*' : self::quote($this->select);
            }
        } else {
            $select = ['*'];
        }


        return join(', ', $select);
    }

    private function buildLimit(): string
    {
        if (!$this->limit) {
            return '';
        }

        list($nbRecords, $page) = $this->limit;

        return sprintf(
            ' LIMIT %d,%d',
            ($page - 1) * $nbRecords,
            $nbRecords
        );
    }

    private function buildOrders(): string
    {
        if (!$this->orders) {
            return "";
        }

        $orders = [];
        foreach ($this->orders as $order) {
            /** @var DBRecordRow $datatableRow */
            /** @var bool $asc */
            list($datatableRow, $asc) = $order;

            if ($this->requireDatatable()) {
                $orders[] = sprintf(
                    '%s.%s %s',
                    self::quote($datatableRow->datatable()),
                    self::quote($datatableRow),
                    $asc ? 'ASC' : 'DESC'
                );
            } else {
                $orders[] = sprintf(
                    '%s %s',
                    self::quote($datatableRow),
                    $asc ? 'ASC' : 'DESC'
                );
            }
        }

        return sprintf(' ORDER BY %s', implode(', ', $orders));
    }

    public function datatable(DBDatatable $datatable, mixed $filters = null, bool $strict = false): static
    {
        $this->datatables[$datatable->name()] = $datatable;
        if($filters){
            if($strict){
                $this->datatablesInnerFilters[$datatable->name()] = $filters;
            }
            else{
                $this->datatablesLeftFilters[$datatable->name()] = $filters;
            }
        }

        return $this;
    }

    /**
     * @return DBDatatable[]
     */
    public function datatables(): array
    {
        return $this->datatables;
    }

    public function filter(DBRecordRow $datatableRow): static
    {
        $this->filterRows[] = $datatableRow;
        $this->datatable(new DBDatatable($datatableRow->datatable()));

        foreach ($datatableRow->filters() as $filter) {
            if ($filter->value() instanceof DBRecordColumn) {
                $this->datatable(new DBDatatable($filter->value()->datatable()));
            }
        }

        return $this;
    }

    public function select(DBRecordColumn $DBRecordColumn): static
    {
        $this->select[] = $DBRecordColumn;
        $this->datatable(new DBDatatable($DBRecordColumn->datatable()));

        return $this;
    }

    public function limit(int $nbRecords = 1, int $page = 1): static
    {
        $this->limit = [$nbRecords, $page];

        return $this;
    }


    private function order(DBRecordColumn $DBRecordColumn, bool $ascending): static
    {
        $this->orders[] = [$DBRecordColumn, $ascending];
        $this->datatable(new DBDatatable($DBRecordColumn->datatable()));

        return $this;
    }

    public function orderAsc(DBRecordColumn $DBRecordColumn): static
    {
        return $this->order($DBRecordColumn, true);
    }

    public function orderDesc(DBRecordColumn $DBRecordColumn): static
    {
        return $this->order($DBRecordColumn, false);
    }

    private function buildTables(): string
    {
        $output = [];

        foreach($this->datatables as $datatableName=>$datatable){
            if(array_key_exists($datatableName, $this->datatablesLeftFilters)){
                $this->leftFilters = [];
                $this->buildLeftFilters($this->datatablesLeftFilters[$datatableName], true);
                $output[] = sprintf(
                    ' LEFT JOIN %s%s',
                    self::quote($datatable->name()),
                    join('',$this->leftFilters)

                );
            }
            elseif(array_key_exists($datatableName, $this->datatablesInnerFilters)){
                $this->innerFilters = [];
                $this->buildInnerFilters($this->datatablesInnerFilters[$datatableName], true);
                $output[] = sprintf(
                    ' INNER JOIN %s%s',
                    self::quote($datatable->name()),
                    join('',$this->innerFilters)

                );
            }
            else{
                $output[] = (sizeof($output)?', ':'').self::quote($datatable->name());
            }
        }


        return implode('', $output);
    }

}