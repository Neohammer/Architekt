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

    public function toJson(): string
    {
        return json_encode([
            'name' => $this->name,
            'columns' => array_map(function ($column) {
                return $column->toArray();
            }, $this->columns)
        ]);
    }

    public static function fromJson(string $json): static
    {
        $datas = json_decode($json, true);

        $datatable = new DBDatatable($datas['name']);

        foreach ($datas['columns'] as $column) {
            $datatable->addColumn(DBDatatableColumn::fromArray($column));
        }

        return $datatable;
    }
}