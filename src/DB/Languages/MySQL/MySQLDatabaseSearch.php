<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\Interfaces\DBDatabaseInterface;
use Architekt\DB\Interfaces\DBDatabaseSearchInterface;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLDatabaseSearch extends MySQLTools implements DBQueryBuilderInterface, DBDatabaseSearchInterface
{
    private ?DBDatabaseInterface $database;

    public function __construct()
    {
        $this->database = null;
    }

    public function query(): Query
    {
        $filter = '';
        $params = [];
        if ($this->database) {
            $filter = ' LIKE :databaseName';
            $params = [
                ':databaseName' => $this->database->name()
            ];
        }

        return new Query(
            sprintf(
                'SHOW DATABASES%s',
                $filter
            ),
            $params
        );
    }

    public function filter(DBDatabaseInterface $database): static
    {
        $this->database = $database;

        return $this;
    }
}