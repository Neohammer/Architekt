<?php

class HelperView
{
    public static function now(): bool
    {
        return date('Y-m-d');
    }

    public static function nowHour(): bool
    {
        return date('H');
    }

    public static function isObject(mixed $param): bool
    {
        return is_object($param);
    }
}