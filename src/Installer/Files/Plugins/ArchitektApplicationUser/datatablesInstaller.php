<?php

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;

$datatablesToInstall[] = (new DBDatatable('access'))
    ->addColumn(DBDatatableColumn::buildAutoincrement())
    ->addColumn(DBDatatableColumn::buildInt('profile_id', 3))
    ->addColumn(DBDatatableColumn::buildInt('controller_id', 5))
    ->addColumn(DBDatatableColumn::buildString('access', 50));

$datatablesToInstall[] = (new DBDatatable('profile'))
    ->addColumn(DBDatatableColumn::buildAutoincrement())
    ->addColumn(DBDatatableColumn::buildInt('application_id', 3))
    ->addColumn(DBDatatableColumn::buildBoolean('user')->setDefault(true))
    ->addColumn(DBDatatableColumn::buildString('name', 60))
    ->addColumn(DBDatatableColumn::buildString('settings', 10000, true)->setDefault(null))
    ->addColumn(DBDatatableColumn::buildBoolean('default')->setDefault(0));

if ($this->application->hasCustomUser()) {
    $datatablesToInstall[] = (new DBDatatable(strtolower($this->application->user())))
        ->addColumn(DBDatatableColumn::buildAutoincrement())
        ->addColumn(DBDatatableColumn::buildInt('user_id', 5))
        ->addColumn(DBDatatableColumn::buildInt('profile_id', 5))
        ->addColumn(DBDatatableColumn::buildString('hash', 32));
}