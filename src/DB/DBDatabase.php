<?php

namespace Architekt\DB;

use Architekt\DB\Interfaces\DBDatabaseInterface;

class DBDatabase implements DBDatabaseInterface
{
    private string $name;
    private ?string $charset;
    /** @var DBDatatableColumn[] */
    private array $columns;

    public function __construct(
        string  $name,
        ?string $charset = null
    )
    {
        $this->name = $name;
        $this->charset = $charset;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function charset(): ?string
    {
        return $this->charset;
    }

    public function toJson(): string
    {
        return json_encode([
            'name' => $this->name,
            'charset' => $this->charset
        ]);
    }

    public static function fromJson(string $json): static
    {
        $datas = json_decode($json, true);

        return new self(
            $datas['name'],
            $datas['charset']
        );
    }
}