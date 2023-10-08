<?php

namespace Architekt\DB;

class DBDatatableColumn
{

    const TYPE_NUMERIC = 'numeric';
    const TYPE_STRING = 'string';
    const TYPE_DATETIME = 'datetime';
    const TYPE_BOOLEAN = 'boolean';


    private string $name;
    private string $type;
    private ?int $length;
    private ?int $size;
    private null|string|bool $default;
    private bool $nullable;
    private bool $primary;
    private bool $multiLines;
    private bool $unsigned;
    private bool $autoincrement;
    private bool $hasDefault;


    private function __construct(
        string $name,
        string $type
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->primary = false;
        $this->hasDefault = false;
        $this->nullable = false;
        $this->autoincrement = false;
    }

    public static function build(string $name, string $type): static
    {
        return new self($name, $type);
    }

    public static function buildPrimary(string $name = 'id'): static
    {
        return
            (new self(
                $name,
                self::TYPE_NUMERIC
            ))
                ->setPrimary();
    }

    public static function buildAutoincrement(string $name = 'id'): static
    {
        return
            (self::buildPrimary($name))
                ->setAutoincrement();
    }

    public static function buildString(string $name, int $length, bool $multiLines = false): static
    {
        return
            (new self(
                $name,
                self::TYPE_STRING
            ))
                ->setLength($length)
                ->setMultiLines($multiLines);
    }

    public static function buildDatetime(string $name): static
    {
        return
            (new self(
                $name,
                self::TYPE_DATETIME
            ));
    }

    public static function buildInt(string $name, int $length, bool $signed = false): static
    {
        return
            (new self(
                $name,
                self::TYPE_NUMERIC
            ))
                ->setLength($length)
                ->setUnsigned(!$signed);
    }

    public static function buildBoolean(string $name): static
    {
        return
            (new self(
                $name,
                self::TYPE_BOOLEAN
            ));
    }

    public function autoincrement(): bool
    {
        return $this->autoincrement;
    }

    public function setAutoincrement(): static
    {
        $this
            ->setNullable(false)
            ->setUnsigned()
            ->autoincrement = true;

        return $this;
    }

    public function hasDefault(): null|string|bool
    {
        return $this->hasDefault;
    }

    public function default(): null|string|bool
    {
        return $this->default;
    }

    public function setDefault(null|string|bool $default): static
    {
        $this->hasDefault = true;
        $this->default = $default;

        if ($default === null) {
            $this->setNullable();
        }

        return $this;
    }

    public function length(): int
    {
        return $this->length;
    }

    private function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function multiLines(): bool
    {
        return $this->multiLines;
    }

    private function setMultiLines(bool $multiLines = true): static
    {
        $this->multiLines = $multiLines;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function primary(): bool
    {
        return $this->primary;
    }

    public function setPrimary(): static
    {
        $this->primary = true;

        return $this;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function unsigned(): bool
    {
        return $this->unsigned;
    }

    public function setUnsigned(): static
    {
        $this->unsigned = true;

        return $this;
    }


    public function toArray(): array
    {
        $properties = [
            'name', 'type', 'length', 'size', 'default', 'nullable', 'primary', 'multiLines', 'unsigned', 'autoincrement', 'hasDefault'
        ];

        $array = [];
        foreach ($properties as $property) {

            if (isset($this->$property)) {
                $array[$property] = $this->$property;
            }
        }

        return $array;
    }

    public static function fromArray(array $datas): static
    {
        $that = new self($datas['name'], $datas['type']);
        foreach ($datas as $k => $v) {
            $that->$k = $v;
        }

        return $that;
    }

}