<?php

namespace Users;

use App;
use Architekt\Logger;
use Notifications\EmailNotification;
use Notifications\InternalNotification;

class UserNotification
{
    public static function pushCreateApply(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/created',
            ['user' => $user->_primary()]
        );
    }

    public static function pushCreatedWithToken(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/createdWithToken',
            ['user' => $user->_primary()]
        );
    }

    public static function pushCreateWithToken(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/createWithToken',
            ['user' => $user->_primary()]
        );

        EmailNotification::build(
            $user->_get('email'),
            "Confirmation de la création de votre compte",
            "User/createWithTokenConfirm",
            [
                'USER' => $user,
                'LINK' => $link = sprintf(
                    '%sUser/createConfirm/%s/%s',
                    App::url(),
                    $user->_primary(),
                    (Token::buildAccountWithConfirmationCreate($user))->key()
                )
            ],
        );
        Logger::info($link);
    }

    public static function pushLogin(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/login',
            ['user' => $user->_primary()]
        );
    }

    public static function pushLogout(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/logout',
            ['user' => $user->_primary()]
        );
    }

    public static function pushPasswordRecover(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/passwordRecover',
            ['user' => $user->_primary()]

        );

        EmailNotification::build(
            $user->_get('email'),
            "Demande de récupération de mot de passe",
            "User/passwordRecoverConfirm",
            [
                'USER' => $user,
                'LINK' => $link = sprintf(
                    '%sUser/passwordRecoverConfirm/%s/%s',
                    App::url(),
                    $user->_primary(),
                    (Token::buildPasswordRecover($user))->key()
                )
            ],
        );

        Logger::info($link);
    }

    public static function pushPasswordRecovered(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/passwordRecovered',
            ['user' => $user->_primary()]
        );
    }


    public static function pushPasswordChoose(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/passwordChoose',
            ['user' => $user->_primary()]

        );

        EmailNotification::build(
            $user->_get('email'),
            "Demande de création de mot de passe",
            "User/passwordChooseConfirm",
            [
                'USER' => $user,
                'LINK' => $link = sprintf(
                    '%sUser/passwordChoose/%s/%s',
                    App::url(),
                    $user->_primary(),
                    (Token::buildPasswordRecover($user))->key()
                )
            ],
        );

        Logger::info($link);
    }

    public static function pushPasswordChosen(User $user): void
    {
        InternalNotification::build(
            'admin',
            'User/passwordChoosen',
            ['user' => $user->_primary()]
        );
    }
}