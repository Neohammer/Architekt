<?php

namespace Architekt\DB\Engines;

use Architekt\DB\Motors\DBMotorInterface;
use Architekt\DB\Motors\Mysql;
use PDOStatement;

class PDO implements DBEngineInterface
{
    private \PDO $connexion;
    private string $hostname;
    private string $user;
    private string $password;
    private string $database;
    private ?int $port;
    private string $charset;
    private ?string $last;
    /**
     * @var PDOStatement[]
     */
    private array $queries;
    private array $queries_log;

    public function configure(
        string  $hostname,
        string  $user,
        string  $password,
        string  $database,
        ?int    $port = null,
        ?string $charset = 'UTF-8'
    ): DBEngineInterface
    {
        $this->hostname = $hostname;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
        $this->port = $port ?? 3306;
        $this->queries = [];
        $this->queries_log = [];

        return $this;
    }

    public function database(): string
    {
        return $this->database;
    }

    public function motor(): DBMotorInterface
    {
        return new Mysql();
    }

    public function escape(string $text): string
    {
        return $text;
    }

    public function quote(string $field): string
    {
        return $this->connexion()->quote($field);
    }

    private function connexion(): \PDO
    {
        if (!isset($this->connexion)) {
            $this->connexion = new \PDO(
                sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s;port=%s",
                    $this->hostname,
                    $this->database,
                    $this->charset,
                    $this->port,
                ),
                $this->user,
                $this->password
            );

            $this->last = null;
        }

        return $this->connexion;
    }

    public function execute(string $query): string
    {
        $hash = uniqid();
        $this->queries[$hash] = $this->connexion()->query($query);
        $this->queries_log[$hash] = $query;

        return $hash;
    }

    public function startTransaction(): bool
    {
        return $this->connexion()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0)
            && $this->connexion()->beginTransaction();
    }

    public function commitTransaction(): bool
    {
        return $this->connexion()->commit()
            && $this->enableAutocommit();
    }

    public function rollbackTransaction(): bool
    {
        return $this->connexion()->rollBack()
            && $this->enableAutocommit();
    }

    public function last(): ?string
    {
        return $this->last;
    }

    public function lastInsertId(): ?string
    {
        return $this->connexion()->lastInsertId() ? $this->connexion()->lastInsertId() : null;
    }

    public function fetch(string $queryIdentifier): ?array
    {
        if(!$this->queryIsSuccess($queryIdentifier)) {
            return null;
        }

        $fetched = $this->queries[$queryIdentifier]->fetch(\PDO::FETCH_ASSOC);

        return false === $fetched ? null : $fetched;
    }

    public function resultsNb(string $queryIdentifier): int
    {
        return $this->queries[$queryIdentifier]->rowCount();
    }

    public function disableAutocommit(): bool
    {
        return $this->connexion()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
    }

    public function enableAutocommit(): bool
    {
        return $this->connexion()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
    }

    public function queryIsSuccess(string $queryIdentifier): bool
    {
        return false !== $this->queries[$queryIdentifier];
    }

    public function queryError(string $queryIdentifier): ?string
    {
        if($this->queryIsSuccess($queryIdentifier)) {
            return null;
        }

        return $this->queries[$queryIdentifier]->errorInfo()[2];
    }

    public function query(string $queryIdentifier): ?string
    {
        return $this->queries_log[$queryIdentifier] ?: null;
    }
}