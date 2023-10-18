<?php

namespace Architekt\DB\Interfaces;

interface DBEntityInterface
{
    public function __construct(?int $primary = null);

    public function _databaseIdentifier(): string;

    public function _database(): string;

    public function _isEqualTo(DBEntityInterface $entity): bool;

    public function _isSameClass(mixed $entity): bool;

    public function _get(?string $key = null, ?bool $originalDatas = false): mixed;

    public function _isLoaded(): bool;

    public function _compare(): array;

    public function _primary(): string|int|null;

    public function _primaryKey(): string;

    public function _strangerKey(): string;

    public function _table(bool $withPrefix = true): string;
}