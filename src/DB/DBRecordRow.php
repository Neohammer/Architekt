<?php

namespace Architekt\DB;

use Architekt\DB\Interfaces\DBRecordRowInterface;

class DBRecordRow implements DBRecordRowInterface
{
    private string $datatableName;
    private array $values;
    private array $filters;

    public function __construct(string $datatableName)
    {
        $this->datatableName = $datatableName;
        $this->values = [];
        $this->filters = [];
    }

    public static function populate(string $datatableName, array $values): static
    {
        return (new static($datatableName))->aset($values);
    }

    public function aset(array $values): static
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    public function set(string $key, mixed $value): static
    {
        $this->values[$key] = $value;

        return $this;
    }

    public function aget(): array
    {
        return $this->values;
    }

    public function values(): array
    {
        return $this->values;
    }

    public function datatable(): string
    {
        return $this->datatableName;
    }

    public function and(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAnd($name, $value);

        return $this;
    }

    public function andGreater(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndGreater($name, $value);

        return $this;
    }

    public function andGreaterOrEqual(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndGreaterOrEqual($name, $value);

        return $this;
    }

    public function andLower(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndLower($name, $value);

        return $this;
    }

    public function andLowerOrEqual(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndLowerOrEqual($name, $value);

        return $this;
    }

    public function andNot(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndNot($name, $value);

        return $this;
    }

    /**
     * @return DBRecordRowFilter[]
     */
    public function filters(): array
    {
        return $this->filters;
    }
}