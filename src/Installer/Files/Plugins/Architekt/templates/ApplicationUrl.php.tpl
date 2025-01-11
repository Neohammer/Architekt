<?php

class Url{$APPLICATION_CAMEL}
{
    public static function home(): string
    {
        return (new \Website\Admin\Index\IndexController())->__uri();
    }

    public static function menu1(): string
    {
        return (new \Website\Admin\Index\IndexController())->__uri();
    }

    public static function menu2(): string
    {
        return (new \Website\Admin\Index\IndexController())->__uri();
    }

    public static function menu3(): string
    {
        return (new \Website\Admin\Index\IndexController())->__uri();
    }
}