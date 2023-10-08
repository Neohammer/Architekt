<?php

namespace Architekt\DB;

use Architekt\DB\Interfaces\DBDatatableInterface;

class DBDatatable implements DBDatatableInterface
{
    private string $name;
    /** @var DBDatatableColumn[] */
    private array $columns;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->columns = [];
    }

    public function name(): string
    {
        return $this->name;
    }

    public function prefix(string $prefix): static
    {
        $this->name = $prefix.$this->name;

        return $this;
    }

    public function addColumn(DBDatatableColumn $column): static
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @return DBDatatableColumn[]
     */
    public function columns(): array
    {
        return $this->columns;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'columns' => array_map(function ($column) {
                return $column->toArray();
            }, $this->columns)
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public static function fromJson(string $json): static
    {
        return self::fromArray(json_decode($json, true));
    }

    public static function fromArray(array $array): static
    {
        $datatable = new DBDatatable($array['name']);

        foreach ($array['columns'] as $column) {
            $datatable->addColumn(DBDatatableColumn::fromArray($column));
        }

        return $datatable;
    }
}