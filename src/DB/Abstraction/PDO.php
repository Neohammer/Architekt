<?php

namespace Architekt\DB\Abstraction;

use Architekt\DB\DBConnexion;
use Architekt\DB\DBDatabase;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBRecordRow;
use Architekt\DB\DBRecordSearchFetcher;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Interfaces\DBAbstractionInterface;
use Architekt\DB\Interfaces\DBDatabaseSearchInterface;
use Architekt\DB\Interfaces\DBDatatableSearchInterface;
use Architekt\DB\Interfaces\DBLanguageInterface;
use Architekt\DB\Interfaces\DBRecordRowInterface;
use Architekt\DB\Interfaces\DBRecordSearchInterface;
use Architekt\DB\Languages\MySQL;

class PDO implements DBAbstractionInterface
{
    private DBConnexion $DBConnexion;

    private ?\PDO $PDOObject;

    private DBLanguageInterface $requestBuilder;

    private string $dsn;

    private ?DBDatabaseSearchInterface $databaseSearch;

    private ?DBDatatableSearchInterface $datatableSearch;

    public function __construct(DBConnexion $DBConnexion)
    {
        $this->DBConnexion = $DBConnexion;
        $this->PDOObject = null;
        $this->datatableSearch = null;

        if ($DBConnexion->language() === DBConnexion::MYSQL) {
            $this->requestBuilder = new MySQL();

            $this->dsn = 'mysql:host=%1$s;dbname=%4$s;charset=%2$s;port=%3$s';
        } else {
            throw new MissingConfigurationException(sprintf('%s language is not supported by PDO abstraction yet', $DBConnexion->language()));
        }
    }

    private function pdo(): \PDO
    {
        $this->datatableSearch = null;

        if (!$this->PDOObject) {
            $parameters = $this->DBConnexion->parameters();

            $this->PDOObject = new \PDO(
                sprintf(
                    $this->dsn,
                    $parameters['hostname'],
                    $parameters['charset'],
                    $parameters['port'] ?? 3306,
                    $parameters['database'] ?? ''
                ),
                $parameters['user'],
                $parameters['password']
            );
            $this->PDOObject->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
            $this->PDOObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $this->PDOObject;
    }

    public function query(Query $query): bool
    {
        return $this
            ->pdo()
            ->prepare($query->command)
            ->execute($query->params);
    }


    public function databaseCreate(DBDatabase $database): bool
    {
        return $this->query(
            $this->requestBuilder->databaseCreate($database)
        );
    }

    public function databaseDelete(DBDatabase $database): bool
    {
        return $this->query(
            $this->requestBuilder->databaseDelete($database)
        );
    }

    public function databaseExists(DBDatabase $database): bool
    {
        $query = $this->requestBuilder->databaseExists($database);
        $statement = $this
            ->pdo()
            ->prepare($query->command);


        $statement->execute($query->params);

        return $statement->rowCount() > 0;
    }

    public function databaseSearch(): DBDatabaseSearchInterface
    {
        return $this->databaseSearch = $this->requestBuilder->databaseSearch();
    }

    public function databaseSearchApply(): bool
    {
        return $this->query(
                $this->databaseSearch->query()
            ) && $this->databaseSearch = null;
    }


    public function datatableCreate(DBDatatable $datatable): bool
    {
        return $this->query(
            $this->requestBuilder->datatableCreate($datatable)
        );
    }

    public function datatableDelete(DBDatatable $datatable): bool
    {
        return $this->query(
            $this->requestBuilder->datatableDelete($datatable)
        );
    }

    public function datatableEmpty(DBDatatable $datatable): bool
    {
        return $this->query(
            $this->requestBuilder->datatableEmpty($datatable)
        );
    }

    public function datatableExists(DBDatatable $datatable): bool
    {
        $query = $this->requestBuilder->datatableExists($datatable);
        $statement = $this
            ->pdo()
            ->prepare($query->command);


        $statement->execute($query->params);

        return $statement->rowCount() > 0;
    }

    public function datatableSearch(): DBDatatableSearchInterface
    {
        $this->datatableSearch = $this->requestBuilder->datatableSearch();

        return $this->datatableSearch;
    }

    public function datatableSearchApply(): bool
    {
        return $this->query(
                $this->datatableSearch->query()
            ) && $this->datatableSearch = null;
    }


    public function recordDelete(DBRecordRow $recordRow): bool
    {
        return $this->query(
            $this->requestBuilder->recordDelete($recordRow)
        );
    }

    public function recordInsert(DBRecordRow $recordRow): bool
    {
        return $this->query(
            $this->requestBuilder->recordInsert($recordRow)
        );
    }

    public function recordInsertLast(): ?int
    {
        return $this->pdo()->lastInsertId() ?: null;
    }

    public function recordSearch(): DBRecordSearchInterface
    {
        return $this->requestBuilder->recordSearch();
    }

    public function recordUpdate(DBRecordRow $recordRow): bool
    {
        return $this->query(
            $this->requestBuilder->recordUpdate($recordRow)
        );
    }

    public function recordSearchNext(DBRecordSearchFetcher $recordSearchFetcher): bool|DBRecordRowInterface
    {
        $statement = $recordSearchFetcher->statement;

        if (!$statement) {
            return false;
        }

        $result = $recordSearchFetcher->statement->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        return DBRecordRow::populate(current($recordSearchFetcher->recordSearch->datatables())->name(), $result);

    }

    public function recordSearchFetcher(DBRecordSearchInterface $recordSearch): bool|DBRecordSearchFetcher
    {
        $query = $recordSearch->query();
        $statement = $this
            ->pdo()
            ->prepare($query->command);

        if ($statement->execute($query->params)) {
            return new DBRecordSearchFetcher(
                $recordSearch,
                $statement
            );
        }

        return false;
    }

    public function recordSearchCount(DBRecordSearchFetcher $recordSearchFetcher): ?int
    {
        return $recordSearchFetcher->statement->rowCount();
    }

    public function transactionStart(): bool
    {
        return $this->pdo()->beginTransaction();
    }

    public function transactionCommit(): bool
    {
        return $this->pdo()->commit();
    }

    public function transactionRollBack(): bool
    {
        return $this->pdo()->rollBack();
    }

    public function close(): bool
    {
        return true;
    }
}