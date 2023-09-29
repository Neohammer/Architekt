<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBRecordRow;

interface DBLanguageInterface
{
    public function databaseCreate(DBDatabaseInterface $database): Query;

    public function databaseDelete(DBDatabaseInterface $database): Query;

    public function databaseExists(DBDatabaseInterface $database): Query;

    public function databaseSearch(): DBDatabaseSearchInterface;


    public function datatableCreate(DBDatatableInterface $datatable): Query;

    public function datatableDelete(DBDatatableInterface $datatable): Query;

    public function datatableEmpty(DBDatatableInterface $datatable): Query;

    public function datatableExists(DBDatatableInterface $datatable): Query;

    public function datatableSearch(): DBDatatableSearchInterface;


    public function recordDelete(DBRecordRow $recordRow): Query;

    public function recordInsert(DBRecordRow $recordRow): Query;

    public function recordUpdate(DBRecordRow $recordRow): Query;

    public function recordSearch(): DBRecordSearchInterface;
}