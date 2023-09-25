<?php

namespace Architekt\Library;

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

    const PATH = PATH_FILER . '/Logs/%s.log';
    const MESSAGE_FORMAT = '%s;%s;%s;%s;%s;%s';

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

        self::addRow('php-%s', $message, $type);
        return true;//self::TYPE_NOTICE !== $type;
    }

    public static function addMysqlError(
        string $errstr,
        string $request
    ): bool
    {
        $message = sprintf(
            '%s;%s',
            $errstr,
            $request
        );

        self::addRow('mysql-%s', $message, self::TYPE_CRITICAL);

        return true;
    }

    private static function addRow(string $file, string $message, string $type): void
    {
        $filename = sprintf(
            self::PATH,
            $file
        );
        @file_put_contents(
            sprintf(
                $filename,
                date('Y-m-d')
            ),
            Logger . phpself::buildRow(
                $message,
                $type
            ),
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

}