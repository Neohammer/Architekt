<?php

namespace Architekt\View;

class Message
{
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'danger';

    private static function add(string $type,string $text): void
    {
        $_SESSION['message'] = [
            'type' => $type,
            'text' => $text
        ];
    }

    public static function addSuccess(string $text): void
    {
        self::add(self::TYPE_SUCCESS,$text);
    }
    public static function addError(string $text): void
    {
        self::add(self::TYPE_ERROR,$text);
    }

    public static function get(): ?array
    {
        if (array_key_exists('message' , $_SESSION)) {
           $message =  $_SESSION['message'];
           unset($_SESSION['message']);
           return $message;
        }
        return null;
    }

}