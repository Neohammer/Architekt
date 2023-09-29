<?php

namespace Architekt\DB\Interfaces;

interface DBDatabaseInterface
{
    public function __construct(
        string  $name,
        ?string $charset = null
    );

    public function name(): string;

    public function charset(): ?string;

    public function toJson(): string;

    public static function fromJson(string $json): static;

}