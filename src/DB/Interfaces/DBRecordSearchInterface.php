<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBDatatable;
use Architekt\DB\DBRecordColumn;
use Architekt\DB\DBRecordRow;

interface DBRecordSearchInterface extends DBQueryBuilderInterface
{
    public function datatable(DBDatatable $datatable, mixed $filters = null, bool $strict = false): static;

    /**
     * @return DBDatatable[]
     */
    public function datatables(): array;

    public function filter(DBRecordRow $datatableRow): static;

    public function select(DBRecordColumn $DBRecordColumn): static;

    public function limit(int $nbRecords = 1, int $page = 1): static;

    public function orderAsc(DBRecordColumn $DBRecordColumn): static;

    public function orderDesc(DBRecordColumn $DBRecordColumn): static;
}