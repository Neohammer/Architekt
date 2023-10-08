<?php

namespace Events;

use Architekt\Auth\User;

class UserEvent
{
    public static function onCreate(User $user): void
    {

    }

    public static function onCreateTryWithConfirmation(User $user): void
    {

    }

    public static function onCreateConfirmation(User $user): void
    {

    }

    public static function onLogin(User $user): void
    {

    }

    public static function onLogout(User $user): void
    {

    }

    public static function onPasswordTryRecover(User $user): void
    {

    }

    public static function onPasswordRecover(User $user): void
    {

    }

    public static function onPasswordChange(User $user): void
    {

    }

    public static function onPasswordChangeFail(User $user): void
    {

    }
}