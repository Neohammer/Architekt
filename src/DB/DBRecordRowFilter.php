<?php

namespace Architekt\DB;

class DBRecordRowFilter
{
    public const TYPE_AND = 'AND';
    public const TYPE_OR = 'OR';

    public const EGALITY_CONTAINS = 'contains';
    public const EGALITY_EQUAL = 'equal';
    public const EGALITY_GREATER = 'greater';
    public const EGALITY_GREATER_OR_EQUAL = 'greaterOrEqual';

    private string $key;
    private mixed $value;
    private string $type;
    private bool $affirmative;
    private string $egalityType;

    private function __construct(
        string $key,
        mixed  $value,
        string $type,
        bool   $affirmative,
        string $egalityType
    )
    {
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
        $this->affirmative = $affirmative;
        $this->egalityType = $egalityType;
    }


    public static function buildAnd(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            true,
            self::EGALITY_EQUAL
        );
    }

    public static function buildAndGreater(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            true,
            self::EGALITY_GREATER
        );
    }

    public static function buildAndGreaterOrEqual(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            true,
            self::EGALITY_GREATER_OR_EQUAL
        );
    }

    public static function buildAndLower(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            false,
            self::EGALITY_GREATER
        );
    }

    public static function buildAndLowerOrEqual(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            false,
            self::EGALITY_GREATER_OR_EQUAL
        );
    }

    public static function buildOr(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_OR,
            true,
            self::EGALITY_EQUAL
        );
    }

    public static function buildAndNot(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            false,
            self::EGALITY_EQUAL
        );
    }

    public static function buildAndContains(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            true,
            self::EGALITY_CONTAINS
        );
    }

    public static function buildOrContains(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_OR,
            true,
            self::EGALITY_CONTAINS
        );
    }

    public static function buildAndNotContains(
        string $key,
        mixed  $value
    ): static
    {
        return new self(
            $key,
            $value,
            self::TYPE_AND,
            false,
            self::EGALITY_CONTAINS
        );
    }


    public function key(): string
    {
        return $this->key;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function affirmative(): bool
    {
        return $this->affirmative;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function egalityType(): string
    {
        return $this->egalityType;
    }

    public function hasType(string $type): bool
    {
        return $this->type() === $type;
    }

}