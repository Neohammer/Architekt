<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\Interfaces\DBDatabaseInterface;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLDatabaseExists extends MySQLTools implements DBQueryBuilderInterface
{
    private DBDatabaseInterface $database;

    public function __construct(DBDatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function query(): Query
    {
        return (new MySQLDatabaseSearch())
            ->filter($this->database)
            ->query();
    }
}