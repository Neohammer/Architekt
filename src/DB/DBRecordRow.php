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

    public function andContains(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndContains($name, $value);

        return $this;
    }

    public function andNotContains(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAndNotContains($name, $value);

        return $this;
    }

    public function or(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOr($name, $value);

        return $this;
    }

    public function orGreater(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOrGreater($name, $value);

        return $this;
    }

    public function orGreaterOrEqual(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildAOrGreaterOrEqual($name, $value);

        return $this;
    }

    public function orLower(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOrLower($name, $value);

        return $this;
    }

    public function orLowerOrEqual(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOrLowerOrEqual($name, $value);

        return $this;
    }

    public function orNot(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOrNot($name, $value);

        return $this;
    }

    public function orContains(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOrContains($name, $value);

        return $this;
    }

    public function orNotContains(string $name, mixed $value): static
    {
        $this->filters[] = DBRecordRowFilter::buildOrNotContains($name, $value);

        return $this;
    }

    /**
     * @return DBRecordRowFilter[]
     */
    public function filters(): array
    {
        return $this->filters;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'datatable' => $this->datatableName,
            'fields' => $this->values
        ];
    }

    public static function fromJson(string $json): static
    {
        return self::fromArray(json_decode($json, true));
    }

    public static function fromArray(array $array): static
    {
        return (new self($array['datatable']))->aset($array['fields']);
    }
}