<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
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

    /**
     * @return DBRecordRowFilter[]
     */
    public function filters(): array;


    public function toJson(): string;

    public function toArray(): array;

    public static function fromJson(string $json): static;

    public static function fromArray(array $array): static;
}