<?php

namespace Architekt\Auth;

use Architekt\Http\Request;

trait UserLoginTrait
{
    public static function encryptPassword(string $password): string
    {
        return md5($password);
    }

    public static function generateHash(): string
    {
        return md5(time() . uniqid());
    }

    public function sessionRegister(bool $useCookie = false): void
    {
        if ($this->_isLoaded()) {
            $_SESSION[static::SESSION_NAME] = $this->_primary();
            if($useCookie) {
                setcookie(static::SESSION_NAME, $this->user()->_get('hash'), strtotime('+ 7 days'), '/');
                $_COOKIE[static::SESSION_NAME] = $this->user()->_get('hash');
            }
        }
    }

    public static function sessionUnregister(): void
    {
        Request::sessionUnset(static::SESSION_NAME);

        setcookie(static::SESSION_NAME, '', 1, '/');
        if (array_key_exists(static::SESSION_NAME, $_COOKIE)) {
            unset($_COOKIE[static::SESSION_NAME]);
        }
    }
}