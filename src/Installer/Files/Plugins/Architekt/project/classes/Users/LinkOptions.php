<?php

namespace Users;

class LinkOptions
{
    protected static ?array $current = null;

    public static function css(): string
    {
        return self::$current['css'] ?? '';
    }

    public static function container(): bool|string
    {
        return self::$current['container'] ?? false;
    }

    public static function options(): bool
    {
        return self::$current['options'] ?? true;
    }

    public static function label(): bool
    {
        return self::$current['label'] ?? true;
    }

    public static function view(): bool
    {
        return self::$current['view'] ?? true;
    }

    public static function cssOptions(): string
    {
        return sprintf('%s%s', self::css() , 'Mobile');
    }

    public static function cssAction(): string
    {
        return sprintf('%s%s', self::css() , self::$current['cssAction'] ?? '');
    }

    public static function containerStart(): string
    {
        return self::container() ? sprintf('<%s>',self::container()) : '';
    }

    public static function containerEnd(): string
    {
        return self::container() ? sprintf('</%s>',self::container()) : '';
    }
}