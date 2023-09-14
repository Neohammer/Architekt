<?php

namespace Architekt\DB;

interface EntityInterface
{
    public function _databaseIdentifier(): string;

    public function _database(): string;

    public function _isEqualTo(EntityInterface $entity): bool;

    public function _isSameClass(mixed $entity): bool;

    public function _get(?string $key = null): mixed;

    public function _isLoaded(): bool;

    public function _compare(): array;

    public function _primary(): string|int|null;

    public function _primaryKey(): string;

    public function _strangerKey(): string;

    public function _table(bool $withPrefix = true): string;
}