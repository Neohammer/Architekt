<?php

namespace Architekt\DB\Engines;

use Architekt\DB\Motors\DBMotorInterface;
use Architekt\DB\Motors\Mysql;
use mysqli_result;

class Mysqli implements DBEngineInterface
{
    private \mysqli $connexion;
    private string $hostname;
    private string $user;
    private string $password;
    private string $database;
    /**
     * @var mysqli_result[]
     */
    private array $queries;
    private array $queries_log;
    private ?int $port;
    private string $charset;
    private ?string $last;
    private bool $isTransactionStarted;

    public function configure(
        string  $hostname,
        string  $user,
        string  $password,
        string  $database,
        ?int    $port = null,
        ?string $charset = 'UTF8'
    ): DBEngineInterface
    {
        $this->hostname = $hostname;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port ?? 3306;
        $this->charset = $charset;
        $this->queries = [];
        $this->queries_log = [];
        $this->isTransactionStarted = false;

        return $this;
    }

    public function motor(): DBMotorInterface
    {
        return new Mysql();
    }

    public function database(): string
    {
        return $this->database;
    }

    public function escape(string $text): string
    {
        return $this->connexion()->escape_string($text);
    }

    private function connexion(): \mysqli
    {
        if (!isset($this->connexion)) {
            $this->connexion = new \mysqli(
                $this->hostname,
                $this->user,
                $this->password,
                $this->database,
                $this->port
            );

            $this->execute(sprintf(
                'SET NAMES %s',
                $this->charset
            ));

            $this->last = null;
        }

        return $this->connexion;
    }

    public function execute(string $query): string
    {
        $this->last = $query;
        $hash = uniqid();
        $this->queries[$hash] = $this->connexion()->query($query);
        $this->queries_log[$hash] = $query;

        return $hash;
    }

    public function last(): ?string
    {
        return $this->last;
    }

    public function quote(string $field): string
    {
        return sprintf("`%s`", $field);
    }

    public function lastInsertId(): int|string
    {
        return $this->connexion()->insert_id;
    }

    public function startTransaction(): bool
    {
        return  $this->connexion()->begin_transaction(0 );
    }

    public function commitTransaction(): bool
    {
        return $this->connexion()->commit(0);
    }

    public function enableAutocommit(): bool
    {
        return false !== $this->connexion()->query('SET autocommit = 1;');
    }

    public function disableAutocommit(): bool
    {
        return false !== $this->connexion()->query('SET autocommit = 0;');
    }

    public function rollbackTransaction(): bool
    {
        return $this->connexion()->rollback(0);
    }

    public function fetch(string $queryIdentifier): ?array
    {
        if(!$this->queryIsSuccess($queryIdentifier)) return null;

        return $this->queries[$queryIdentifier]->fetch_assoc();
    }

    public function resultsNb(string $queryIdentifier): int
    {
        return $this->queryIsSuccess($queryIdentifier) ? $this->queries[$queryIdentifier]->num_rows : 0;
    }

    public function queryIsSuccess(string $queryIdentifier): bool
    {
        return false !== $this->queries[$queryIdentifier];
    }

    public function query(string $queryIdentifier): ?string
    {
       return $this->queries_log[$queryIdentifier] ?: null;
    }

    public function queryError(string $queryIdentifier): ?string
    {
        if($this->queryIsSuccess($queryIdentifier)) {
            return null;
        }

        return $this->connexion->error;
    }
}