<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBDatatableColumn;

interface DBDatatableInterface
{
    public function __construct(string $name);

    public function name(): string;

    public function addColumn(DBDatatableColumn $column): static;

    /**
     * @return DBDatatableColumn[]
     */
    public function columns(): array;

    public function toJson(): string;

    public static function fromJson(string $json): static;
}