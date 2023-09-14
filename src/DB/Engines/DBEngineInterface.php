<?php

namespace Architekt\DB\Engines;

use Architekt\DB\Motors\DBMotorInterface;

interface DBEngineInterface
{
    public function configure(
        string  $hostname,
        string  $user,
        string  $password,
        string  $database,
        ?int    $port = null,
        ?string $charset = 'UTF-8'
    ): DBEngineInterface;


    public function database(): string;

    public function disableAutocommit(): bool;

    public function escape(string $text): string;

    public function commitTransaction(): bool;

    public function enableAutocommit(): bool;

    public function execute(string $query): string;

    public function queryIsSuccess(string $queryIdentifier): bool;

    public function query(string $queryIdentifier): ?string;

    public function queryError(string $queryIdentifier): ?string;

    public function fetch(string $queryIdentifier): ?array;

    public function last(): ?string;

    public function lastInsertId(): null|int|string;

    public function motor(): DBMotorInterface;

    public function quote(string $field): string;

    public function resultsNb(string $queryIdentifier): int|string;

    public function rollbackTransaction(): bool;

    public function startTransaction(): bool;
}