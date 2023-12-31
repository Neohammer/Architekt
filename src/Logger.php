<?php

namespace Architekt;

class Logger
{
    const TYPE_NOTICE = 'NOTICE';
    const TYPE_WARNING = 'WARN';
    const TYPE_CRITICAL = 'CRITICAL';

    private const PHP_ERRORS = [
        E_NOTICE => self::TYPE_NOTICE,
        E_CORE_ERROR => self::TYPE_CRITICAL,
        E_CORE_WARNING => self::TYPE_WARNING,
        E_COMPILE_ERROR => self::TYPE_CRITICAL,
        E_COMPILE_WARNING => self::TYPE_WARNING,
        E_USER_ERROR => self::TYPE_CRITICAL,
        E_USER_WARNING => self::TYPE_WARNING,
        E_USER_NOTICE => self::TYPE_NOTICE,
        E_STRICT => self::TYPE_WARNING,
        E_RECOVERABLE_ERROR => self::TYPE_CRITICAL,
        E_DEPRECATED => self::TYPE_NOTICE,
        E_USER_DEPRECATED => self::TYPE_NOTICE,
        E_ALL => self::TYPE_NOTICE,
    ];

    private static string $path;
    const PATH_FORMAT = '%s-%%s.log';
    const MESSAGE_FORMAT = '%s;%s;%s;%s;%s;%s';

    public static function setPath(string $path): void
    {
        if(!is_dir($path)){
            mkdir($path);
        }
        self::$path = $path;
    }

    public static function addPhpError(
        int    $errno,
        string $errstr,
        string $errfile,
        int    $errline
    ): bool
    {
        $type = self::PHP_ERRORS[$errno] ?? self::TYPE_WARNING;

        $message = sprintf(
            '%s;%s;%d',
            $errstr,
            $errfile,
            $errline
        );

        self::addRow('php', $message, $type);

        return true;
    }

    protected static function addRow(string $file, string $message, string $type): void
    {
        $filename = sprintf(
            self::$path.DIRECTORY_SEPARATOR.self::PATH_FORMAT,
            $file
        );
        @file_put_contents(
            sprintf(
                $filename,
                date('Y-m-d')
            ),
            self::buildRow(
                $message,
                $type
            )
            . PHP_EOL,
            FILE_APPEND
        );
    }

    private static function buildRow(string $message, string $type): string
    {
        return sprintf(
            self::MESSAGE_FORMAT,
            date('H:i:s'),
            $_SERVER['SERVER_NAME'],
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $type,
            $message,
        );
    }

    public static function info(string $description): void
    {
        self::addRow('web-info', $description, self::TYPE_NOTICE);
    }

    public static function warning(string $description): void
    {
        self::addRow('web-warning', $description, self::TYPE_WARNING);
    }

    public static function critical(string $description): void
    {
        self::addRow('web-critical', $description, self::TYPE_CRITICAL);
    }

}