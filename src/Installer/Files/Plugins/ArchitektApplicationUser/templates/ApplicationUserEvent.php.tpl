<?php

namespace Events;

use Users\{$APPLICATION_USER_CAMEL} as ApplicationUser;

class {$APPLICATION_USER_CAMEL}Event
{
    public static function onCreate(ApplicationUser $user): void
    {

    }

    public static function onCreateByAdministrator(ApplicationUser $user): void
    {

    }
}