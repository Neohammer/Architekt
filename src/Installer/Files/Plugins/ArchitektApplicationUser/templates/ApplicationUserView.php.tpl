<?php

use Users\{$APPLICATION_USER_CAMEL};

class {$APPLICATION_USER_CAMEL}View
{
    public static function {$APPLICATION_USER_LOW}Primary({$APPLICATION_USER_CAMEL} ${$APPLICATION_USER_LOW}): int
    {
        return ${$APPLICATION_USER_LOW}->_primary();
    }

    public static function {$APPLICATION_USER_LOW}Label({$APPLICATION_USER_CAMEL} ${$APPLICATION_USER_LOW}): string
    {
        return ${$APPLICATION_USER_LOW}->label();
    }

    public static function {$APPLICATION_USER_LOW}Email({$APPLICATION_USER_CAMEL} ${$APPLICATION_USER_LOW}): string
    {
        return ${$APPLICATION_USER_LOW}->user()->email();
    }

}