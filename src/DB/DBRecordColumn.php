<?php

namespace Architekt\DB;

class DBRecordColumn
{
    private string $datatableName;
    private string $name;
    private ?string $alias;

    public function __construct(
        string  $datatableName,
        string  $name,
        ?string $alias = null
    )
    {
        $this->datatableName = $datatableName;
        $this->name = $name;
        $this->alias = $alias;
    }

    public function datatable(): string
    {
        return $this->datatableName;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function alias(): ?string
    {
        return $this->alias;
    }

}