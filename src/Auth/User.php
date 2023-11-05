<?php

namespace Architekt\Auth;

use Architekt\DB\DBEntity;
use Architekt\Http\Request;

abstract class User extends DBEntity
{
    public static function encryptPassword(string $password): string
    {
        return md5($password);
    }

    public static function generateHash(): string
    {
        return md5(time() . uniqid());
    }

    public static function loadFromSession(): ?static
    {
        if (array_key_exists(static::SESSION_NAME, $_SESSION) && (int)$_SESSION[static::SESSION_NAME] > 0) {
            $user = new static($_SESSION[static::SESSION_NAME]);
            if ($user->_isLoaded()) {
                return $user;
            }
        }

        if (array_key_exists(static::SESSION_NAME, $_COOKIE)) {
            $User = new static();
            $User->_search()->and($User,'hash', $_COOKIE[static::SESSION_NAME]);
            if ($User->_next()) {
                $User->sessionRegister();
                return $User;
            }
        }
        return null;
    }

    public function profile(): Profile
    {
        return Profile::fromCache($this->_get('profile_id'));
    }
    
    public function sessionRegister(bool $useCookie = false): void
    {
        if ($this->_isLoaded()) {
            $_SESSION[static::SESSION_NAME] = $this->_primary();
            if($useCookie) {
                setcookie(static::SESSION_NAME, $this->_get('hash'), strtotime(static::COOKIE_LIFETIME), '/');
                $_COOKIE[static::SESSION_NAME] = $this->_get('hash');
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

    public function _save(bool $forceInsert = false, ?int $forcePrimary = null): bool
    {
        if (!$this->_isLoaded() || !$this->_get('hash')) {
            $this->_set('hash', static::generateHash());
        }

        return parent::_save($forceInsert, $forcePrimary);
    }
}