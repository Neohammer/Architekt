<?php

namespace Architekt\Installer;

class JsonLocked
{
    public static function init(string $path): array
    {
        if(!file_exists($file = $path.DIRECTORY_SEPARATOR.'architekt.locked')){
            file_put_contents($file , '{}');
        }

        return self::read($path);
    }

    public static function read(string $path): array
    {
        if(!file_exists($file = $path.DIRECTORY_SEPARATOR.'architekt.json')){
            return [];
        }

        return json_decode(file_get_contents($file), true);
    }
}