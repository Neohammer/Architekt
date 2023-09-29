<?php

namespace Architekt\DB;

trait DBEntityCache
{
    static private array $cache = [];

    public static function fromCache(?int $id): static
    {
        if (!array_key_exists($id, self::$cache)) {
            self::$cache[$id] = new static($id);
        }
        return self::$cache[$id];
    }
}