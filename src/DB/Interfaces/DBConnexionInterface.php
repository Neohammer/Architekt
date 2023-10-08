<?php

namespace Architekt\DB\Interfaces;

interface DBConnexionInterface extends DBRequesterInterface
{
    public function __construct(string $name = 'main');

    static public function add(
        string  $name,
        string  $language,
        string  $hostname,
        string  $user,
        string  $password,
        ?string $databaseName = null,
        ?int    $port = null,
        ?string $charset = 'UTF8'
    ): void;

    public static function get(string $name = 'main'): static;

    public function language(): string;

    public function configuration(): array;

    public function parameters(): array;

    public function close(string $name = 'main'): bool;

}