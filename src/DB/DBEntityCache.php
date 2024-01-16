<?php

namespace Architekt\DB;

trait DBEntityCache
{
    static private array $cache = [];

    public static function fromCache(null|int|DBEntity $key): static
    {
        if(!$key){
            return new static;
        }
        if($key instanceof DBEntity){
            $key = $key->_get((new static)->_strangerKey());
        }
        if (!array_key_exists($key, self::$cache)) {
            self::$cache[$key] = new static($key);
        }
        return self::$cache[$key];
    }
}