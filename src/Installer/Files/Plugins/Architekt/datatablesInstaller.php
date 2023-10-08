<?php

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;

$datatablesToInstall[] = (new DBDatatable('user'))
    ->addColumn(DBDatatableColumn::buildAutoincrement())
    ->addColumn(DBDatatableColumn::buildInt('profile_id', 3))
    ->addColumn(DBDatatableColumn::buildString('email', 254))
    ->addColumn(DBDatatableColumn::buildString('password', 32))
    ->addColumn(DBDatatableColumn::buildString('hash', 32))
    ->addColumn(DBDatatableColumn::buildBoolean('confirmed')->setDefault(0))
    ->addColumn(DBDatatableColumn::buildBoolean('active')->setDefault(0));

