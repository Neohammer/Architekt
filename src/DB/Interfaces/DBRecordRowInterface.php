<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBRecordRowFilter;

interface DBRecordRowInterface
{
    public function __construct(string $datatableName);

    public static function populate(string $datatableName, array $values): static;

    public function aset(array $values): static;

    public function set(string $key, mixed $value): static;

    public function aget(): array;

    public function values(): array;

    public function datatable(): string;

    public function and(string $name, mixed $value): static;

    public function andGreater(string $name, mixed $value): static;

    public function andGreaterOrEqual(string $name, mixed $value): static;

    public function andLower(string $name, mixed $value): static;

    public function andLowerOrEqual(string $name, mixed $value): static;

    public function andNot(string $name, mixed $value): static;

    public function andContains(string $name, mixed $value): static;

    public function andNotContains(string $name, mixed $value): static;

    public function andBetween(string $name, mixed $value): static;

    public function andNotBetween(string $name, mixed $value): static;

    public function or(string $name, mixed $value): static;

    public function orGreater(string $name, mixed $value): static;

    public function orGreaterOrEqual(string $name, mixed $value): static;

    public function orLower(string $name, mixed $value): static;

    public function orLowerOrEqual(string $name, mixed $value): static;

    public function orNot(string $name, mixed $value): static;

    public function orContains(string $name, mixed $value): static;

    public function orNotContains(string $name, mixed $value): static;

    public function orBetween(string $name, mixed $value): static;

    public function orNotBetween(string $name, mixed $value): static;

    /**
     * @return DBRecordRowFilter[]
     */
    public function filters(): array;


    public function toJson(): string;

    public function toArray(): array;

    public static function fromJson(string $json): static;

    public static function fromArray(array $array): static;
}