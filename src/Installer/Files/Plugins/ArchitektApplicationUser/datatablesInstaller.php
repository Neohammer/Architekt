<?php

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;

if ($this->application->hasCustomUser()) {
    $datatablesToInstall[] = (new DBDatatable(strtolower($this->application->user())))
        ->addColumn(DBDatatableColumn::buildAutoincrement())
        ->addColumn(DBDatatableColumn::buildInt('user_id', 5))
        ->addColumn(DBDatatableColumn::buildInt('profile_id', 5))
        ->addColumn(DBDatatableColumn::buildString('hash', 32));
}