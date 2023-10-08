<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBDatabase;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBRecordRow;
use Architekt\DB\DBRecordSearchFetcher;

interface DBRequesterInterface
{
    public function query(Query $query): bool;


    public function databaseCreate(DBDatabase $database): bool;

    public function databaseDelete(DBDatabase $database): bool;

    public function databaseExists(DBDatabase $database): bool;

    public function databaseSearch(): DBDatabaseSearchInterface;

    public function databaseSearchApply(): bool;


    public function datatableCreate(DBDatatable $datatable): bool;

    public function datatableDelete(DBDatatable $datatable): bool;

    public function datatableEmpty(DBDatatable $datatable): bool;

    public function datatableExists(DBDatatable $datatable): bool;

    public function datatableSearch(): DBDatatableSearchInterface;

    public function datatableSearchApply(): bool;


    public function recordDelete(DBRecordRow $recordRow): bool;

    public function recordInsert(DBRecordRow $recordRow): bool;

    public function recordInsertLast(): ?int;

    public function recordSearch(): DBRecordSearchInterface;

    public function recordSearchNext(DBRecordSearchFetcher $recordSearchFetcher): bool|DBRecordRowInterface;

    public function recordSearchFetcher(DBRecordSearchInterface $recordSearch): bool|DBRecordSearchFetcher;

    public function recordSearchCount(DBRecordSearchFetcher $recordSearchFetcher): ?int;

    public function recordUpdate(DBRecordRow $recordRow): bool;

    public function transactionStart(): bool;

    public function transactionCommit(): bool;

    public function transactionRollBack(): bool;

}