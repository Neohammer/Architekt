<?php

use Users\LinkOptions;

class Link{$APPLICATION_CAMEL}Options extends LinkOptions
{
    protected static array $config = [
        'optionsModal' => [
            'css' => 'buttonActionModal',
            'container' => 'div',
            'options' => false,
        ],
        'list' => [
            'css' => 'buttonAction',
            'label' => false,
            'view' => false,
            'cssAction' => 'Desktop'
        ],
        'listSmall' => [
            'css' => 'buttonActionSmall',
            'label' => false,
            'view' => false,
            'cssAction' => 'Desktop'
        ],
        'navbarView' => [
            'css' => 'buttonActionBottom',
            'view' => false,
            'cssAction' => 'Desktop'
        ],
        'cardHeader' => [
            'css' => 'buttonActionCardHeader',
            'view' => false,
            'cssAction' => 'Desktop'
        ]
    ];

    public static function init(string $key): void
    {
        self::$current = self::$config[$key] ?? null;
    }
}