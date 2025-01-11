<?php

namespace Architekt\Auth;

trait ApplicationUserLoginTrait
{
    public static function loadFromSession(): ?ApplicationUserInterface
    {
        if ($id = self::retrieveIdFromSession()) {
            $user = new static($id);
            if ($user->_isLoaded()) {
                return $user;
            }
        }

        if ($hash = self::retrieveHashFromCookie()) {
            ($user = new static())
                ->_search()
                ->and($user, 'hash', $hash);

            if ($user->_next()) {
                $user->sessionRegister();
                return $user;
            }
        }

        return null;
    }

    protected static function retrieveIdFromSession(): ?int
    {
        if (array_key_exists(static::SESSION_NAME, $_SESSION) && (int)$_SESSION[static::SESSION_NAME] > 0) {
            return $_SESSION[static::SESSION_NAME];
        }

        return null;
    }

    protected static function retrieveHashFromCookie(): ?string
    {
        if (array_key_exists(static::SESSION_NAME, $_COOKIE)) {
            return $_COOKIE[static::SESSION_NAME];
        }

        return null;
    }
}