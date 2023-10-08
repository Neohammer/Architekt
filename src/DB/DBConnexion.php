<?php

namespace Architekt\DB;

use Architekt\DB\Abstraction\PDO;
use Architekt\DB\Abstraction\Query;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Interfaces\DBAbstractionInterface;
use Architekt\DB\Interfaces\DBConnexionInterface;
use Architekt\DB\Interfaces\DBDatabaseSearchInterface;
use Architekt\DB\Interfaces\DBDatatableSearchInterface;
use Architekt\DB\Interfaces\DBRecordRowInterface;
use Architekt\DB\Interfaces\DBRecordSearchInterface;

class DBConnexion implements DBConnexionInterface
{
    const MYSQL = 'MySQL';

    private static array $connexions = [];
    /** @var static[] $instances */
    private static array $instances = [];

    private string $name;

    private DBAbstractionInterface $abstraction;

    public function __construct(string $name = 'main')
    {
        $this->name = $name;
        $this->abstraction = new PDO($this);
    }

    static public function add(
        string  $name,
        string  $language,
        string  $hostname,
        string  $user,
        string  $password,
        ?string $databaseName = null,
        ?int    $port = null,
        ?string $charset = 'UTF8'
    ): void
    {
        self::$connexions[$name] = [
            'language' => $language,
            'parameters' => [
                'hostname' => $hostname,
                'user' => $user,
                'password' => $password,
                'database' => $databaseName,
                'port' => $port,
                'charset' => $charset,
            ]
        ];

    }

    public static function get(string $name = 'main'): static
    {
        if (!array_key_exists($name, self::$instances)) {
            if (!array_key_exists($name, self::$connexions)) {
                throw new MissingConfigurationException(sprintf(
                    'DBConnexion "%s" is unknown (%s)',
                    $name,
                    implode(', ', array_keys(self::$instances))
                ));
            }
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    public function language(): string
    {
        return $this->configuration()['language'];
    }

    public function configuration(): array
    {
        return self::$connexions[$this->name];
    }

    public function parameters(): array
    {
        return $this->configuration()['parameters'];
    }

    public function query(Query $query): bool
    {
        return $this->abstraction->query($query);
    }

    public function databaseCreate(DBDatabase $database): bool
    {
        return $this->abstraction->databaseCreate($database);
    }

    public function databaseDelete(DBDatabase $database): bool
    {
        return $this->abstraction->databaseDelete($database);
    }

    public function databaseExists(DBDatabase $database): bool
    {
        return $this->abstraction->databaseExists($database);
    }

    public function databaseSearch(): DBDatabaseSearchInterface
    {
        return $this->abstraction->databaseSearch();
    }

    public function databaseSearchApply(): bool
    {
        return $this->abstraction->databaseSearchApply();
    }

    public function datatableCreate(DBDatatable $datatable): bool
    {
        return $this->abstraction->datatableCreate($datatable);
    }

    public function datatableDelete(DBDatatable $datatable): bool
    {
        return $this->abstraction->datatableDelete($datatable);
    }

    public function datatableEmpty(DBDatatable $datatable): bool
    {
        return $this->abstraction->datatableEmpty($datatable);
    }

    public function datatableExists(DBDatatable $datatable): bool
    {
        return $this->abstraction->datatableExists($datatable);
    }

    public function datatableSearch(): DBDatatableSearchInterface
    {
        return $this->abstraction->datatableSearch();
    }

    public function datatableSearchApply(): bool
    {
        return $this->abstraction->datatableSearchApply();
    }

    public function recordDelete(DBRecordRow $recordRow): bool
    {
        return $this->abstraction->recordDelete($recordRow);
    }

    public function recordInsert(DBRecordRow $recordRow): bool
    {
        return $this->abstraction->recordInsert($recordRow);
    }

    public function recordInsertLast(): ?int
    {
        return $this->abstraction->recordInsertLast();
    }

    public function recordSearch(): DBRecordSearchInterface
    {
        return $this->abstraction->recordSearch();
    }

    public function recordUpdate(DBRecordRow $recordRow): bool
    {
        return $this->abstraction->recordUpdate($recordRow);
    }

    public function recordSearchNext(DBRecordSearchFetcher $recordSearchFetcher): bool|DBRecordRowInterface
    {
        return $this->abstraction->recordSearchNext($recordSearchFetcher);
    }

    public function recordSearchFetcher(DBRecordSearchInterface $recordSearch): bool|DBRecordSearchFetcher
    {
        return $this->abstraction->recordSearchFetcher($recordSearch);
    }

    public function recordSearchCount(DBRecordSearchFetcher $recordSearchFetcher): ?int
    {
        return $this->abstraction->recordSearchCount($recordSearchFetcher);
    }

    public function transactionStart(): bool
    {
        return $this->abstraction->transactionStart();
    }

    public function transactionCommit(): bool
    {
        return $this->abstraction->transactionCommit();
    }

    public function transactionRollBack(): bool
    {
        return $this->abstraction->transactionRollBack();
    }

    public function close(string $name = 'main'): bool
    {
        unset(self::$connexions[$name]);
        return $this->abstraction->close();
    }
}