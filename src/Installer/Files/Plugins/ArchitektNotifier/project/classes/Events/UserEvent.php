<?php

namespace Architekt\Events;

use Architekt\Auth\User;
use Architekt\Auth\UserNotification;

class UserEvent
{
    public static function onCreate(User $user): void
    {
        UserNotification::pushCreateApply($user);
    }

    public static function onCreateTryWithConfirmation(User $user): void
    {
        UserNotification::pushCreateWithToken($user);
    }

    public static function onCreateConfirmation(User $user): void
    {
        UserNotification::pushCreatedWithToken($user);
    }

    public static function onLogin(User $user): void
    {
        UserNotification::pushLogin($user);
    }

    public static function onLogout(User $user): void
    {
        UserNotification::pushLogout($user);
    }

    public static function onPasswordTryRecover(User $user): void
    {
        UserNotification::pushPasswordRecover($user);
    }

    public static function onPasswordRecover(User $user): void
    {
        UserNotification::pushPasswordRecovered($user);
    }

    public static function onPasswordChange(User $user): void
    {
        UserNotification::pushPasswordChosen($user);
    }

    public static function onPasswordChangeFail(User $user): void
    {

    }

}