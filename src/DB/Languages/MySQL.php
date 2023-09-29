<?php

namespace Architekt\DB\Languages;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBDatabase;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Interfaces\DBDatabaseInterface;
use Architekt\DB\Interfaces\DBDatabaseSearchInterface;
use Architekt\DB\Interfaces\DBDatatableInterface;
use Architekt\DB\Interfaces\DBDatatableSearchInterface;
use Architekt\DB\Interfaces\DBLanguageInterface;
use Architekt\DB\Interfaces\DBRecordSearchInterface;
use Architekt\DB\Languages\MySQL\MySQLDatabaseCreate;
use Architekt\DB\Languages\MySQL\MySQLDatabaseDelete;
use Architekt\DB\Languages\MySQL\MySQLDatabaseExists;
use Architekt\DB\Languages\MySQL\MySQLDatabaseSearch;
use Architekt\DB\Languages\MySQL\MySQLDatatableCreate;
use Architekt\DB\Languages\MySQL\MySQLDatatableDelete;
use Architekt\DB\Languages\MySQL\MySQLDatatableEmpty;
use Architekt\DB\Languages\MySQL\MySQLDatatableExists;
use Architekt\DB\Languages\MySQL\MySQLDatatableSearch;
use Architekt\DB\Languages\MySQL\MySQLRecordDelete;
use Architekt\DB\Languages\MySQL\MySQLRecordInsert;
use Architekt\DB\Languages\MySQL\MySQLRecordSearch;
use Architekt\DB\Languages\MySQL\MySQLRecordUpdate;

class MySQL implements DBLanguageInterface
{
    public function databaseCreate(DBDatabaseInterface $database): Query
    {
        return (new MySQLDatabaseCreate($database))->query();
    }

    public function databaseDelete(DBDatabaseInterface $database): Query
    {
        return (new MySQLDatabaseDelete($database))->query();
    }

    public function databaseExists(DBDatabaseInterface $database): Query
    {
        return (new MySQLDatabaseExists($database))->query();
    }

    public function databaseSearch(): DBDatabaseSearchInterface
    {
        return new MySQLDatabaseSearch();
    }


    public function datatableCreate(DBDatatableInterface $datatable): Query
    {
        return (new MySQLDatatableCreate($datatable))->query();
    }

    public function datatableDelete(DBDatatableInterface $datatable): Query
    {
        return (new MySQLDatatableDelete($datatable))->query();
    }

    public function datatableEmpty(DBDatatableInterface $datatable): Query
    {
        return (new MySQLDatatableEmpty($datatable))->query();
    }

    public function datatableExists(DBDatatableInterface $datatable): Query
    {
        return (new MySQLDatatableExists($datatable))->query();
    }

    public function datatableSearch(): DBDatatableSearchInterface
    {
        return new MySQLDatatableSearch();
    }


    public function recordDelete(DBRecordRow $recordRow): Query
    {
        return (new MySQLRecordDelete($recordRow))->query();
    }

    public function recordInsert(DBRecordRow $recordRow): Query
    {
        return (new MySQLRecordInsert($recordRow))->query();
    }

    public function recordSearch(): DBRecordSearchInterface
    {
        return new MySQLRecordSearch();
    }

    public function recordUpdate(DBRecordRow $recordRow): Query
    {
        return (new MySQLRecordUpdate($recordRow))->query();
    }
}