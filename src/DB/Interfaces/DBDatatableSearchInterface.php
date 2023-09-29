<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBDatatable;

interface DBDatatableSearchInterface
{
    public function filter(DBDatatable $DBDatatable): static;

}