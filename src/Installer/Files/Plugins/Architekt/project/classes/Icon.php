<?php

class Icon
{

    public static function hidden(?int $size = null, string $class = ''): string
    {
        return self::buildMdi('eye-off', $size, $class);
    }

    static public function link(?int $size = null): string
    {
        return self::globe($size);
    }

    public static function public(?int $size = null, string $class = 'text-success'): string
    {
        return self::buildMdi('lock-open', $size, $class);
    }

    public static function globe(?int $size = null, string $class = 'text-success'): string
    {
        return self::buildFeather('globe', $size);
    }

    public static function creditCard(?int $size = null, string $class = ''): string
    {
        return self::buildFeather('credit-card', $size, $class);
    }

    public static function private(?int $size = null, string $class = 'text-danger'): string
    {
        return self::buildMdi('lock', $size, $class);
    }


    public static function visible(?int $size = null, string $class = ''): string
    {
        return self::buildMdi('eye', $size, $class);
    }

    public static function send(?int $size = null): string
    {
        return self::buildMdi('email', $size);
    }


    public static function delete(?int $size = null): string
    {
        return self::buildMdi('delete', $size);
    }

    public static function date(?int $size = null): string
    {
        return self::buildMdi('calendar-clock', $size);
    }

    public static function info(?int $size = null): string
    {
        return self::buildMdi('information-outline', $size);
    }

    public static function success(?int $size = null): string
    {
        return self::buildMdi('check-circle', $size);
    }

    public static function warning(?int $size = null): string
    {
        return self::buildMdi('alert', $size);
    }

    public static function cancel(?int $size = null, string $class = ''): string
    {
        return self::buildMdi('close-circle', $size, $class);
    }

    public static function valide(?int $size = null, string $class = ''): string
    {
        return self::buildMdi('check-circle', $size, $class);
    }

    public static function save(?int $size = null): string
    {
        return self::buildMdi('floppy', $size);
    }

    public static function euro(?int $size = null): string
    {
        return self::buildMdi('currency-eur', $size);
    }

    public static function logout(?int $size = null): string
    {
        return self::buildMdi('power', $size);
    }

    public static function login(?int $size = null): string
    {
        return self::buildMdi('account', $size);
    }

    public static function fileVideo(?int $size = null): string
    {
        return self::buildMdi('file-video', $size);
    }

    public static function fileMusic(?int $size = null): string
    {
        return self::buildMdi('file-music', $size);
    }

    public static function back(?int $size = null): string
    {
        return self::buildMdi('arrow-left-bold', $size);
    }

    public static function next(?int $size = null): string
    {
        return self::buildMdi('arrow-right-bold', $size);
    }

    public static function filePdf(?int $size = null): string
    {
        return self::buildMdi('file-pdf-box', $size);
    }

    public static function file(?int $size = null): string
    {
        return self::buildMdi('file', $size);
    }


    static public function global(?int $size = null): string
    {
        return self::buildFeather('globe', $size);
    }

    public static function fileView(?int $size = null): string
    {
        return self::buildMdi('eye', $size);
    }

    public static function fileDownload(?int $size = null): string
    {
        return self::buildMdi('download', $size);
    }


    public static function fileUpload(?int $size = null): string
    {
        return self::buildMdi('upload', $size);
    }

    public static function legend(?int $size = null): string
    {
        return self::buildMdi('help-circle-outline', $size);
    }

    public static function add(?int $size = null, string $class = ''): string
    {
        return self::buildMdi('note-plus', $size, $class);
    }

    public static function settings(?int $size = null, string $class = ''): string
    {
        return self::buildMdi('tune-vertical', $size, $class);
    }

    public static function upload(?int $size = null): string
    {
        return self::buildMdi('upload', $size);
    }

    public static function up(?int $size = null): string
    {
        return self::buildMdi('arrow-up-bold', $size);
    }

    public static function down(?int $size = null): string
    {
        return self::buildMdi('arrow-down-bold', $size);
    }


    private static function view(?int $size = null): string
    {
        return self::buildMdi('eye', $size);
    }

    public static function options(?int $size = null): string
    {
        return self::buildMdi('billboard', $size);
    }

    public static function list(?int $size = null): string
    {
        return self::buildMdi('view-list', $size);
    }

    public static function edit(?int $size = null): string
    {
        return self::buildMdi('pencil', $size);
    }

    private static function buildMdi(string $icon, ?int $size, string $class = ''): string
    {
        return sprintf(
            '<i class="mdi mdi-%s%s %s" style="vertical-align: middle"></i>',
            $icon,
            $size ? sprintf(' fs-%d', $size) : '',
            $class
        );
    }

    private static function buildFeather(string $icon, ?int $size, string $class = ''): string
    {
        return sprintf(
            '<i data-feather="%s" class="%s feather-icon-%s"></i>',
            $icon,
            $class,
            $size ?? 4,
        );
    }
}