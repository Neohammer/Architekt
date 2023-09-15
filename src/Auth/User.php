<?php

namespace Architekt\Auth;

use Architekt\DB\Entity;

abstract class User extends Entity
{
    public const SESSION_NAME = 'User';

    public static function encryptPassword(string $password): string
    {
        return md5($password);
    }

    public static function generateHash(): string
    {
        return md5(time() . uniqid());
    }

    public static function loadFromSession(): ?self
    {
        if (array_key_exists(self::SESSION_NAME, $_SESSION) && (int)$_SESSION[self::SESSION_NAME] > 0) {
            $user = new static($_SESSION[self::SESSION_NAME]);
            if ($user->_isLoaded()) {
                return $user;
            }
        }

        if (array_key_exists(self::SESSION_NAME, $_COOKIE)) {
            $User = new static();
            $User->_search()->filter('hash', $_COOKIE[self::SESSION_NAME]);
            if ($User->_next()) {
                $User->sessionRegister();
                return $User;
            }
        }
        return null;
    }

    public function sessionRegister(): void
    {
        if ($this->_isLoaded()) {
            $_SESSION[self::SESSION_NAME] = $this->_primary();
            setcookie(self::SESSION_NAME, $this->_get('hash'), strtotime('+ 7 days'), '/');
            $_COOKIE[self::SESSION_NAME] = $this->_get('hash');
        }
    }

    public static function sessionUnregister(): void
    {
        if (array_key_exists(self::SESSION_NAME, $_SESSION)) {
            unset($_SESSION[self::SESSION_NAME]);
        }
        setcookie(self::SESSION_NAME, '', 1, '/');
        if (array_key_exists(self::SESSION_NAME, $_COOKIE)) {
            unset($_COOKIE[self::SESSION_NAME]);
        }
    }

    public function _save(bool $forceInsert = false): bool
    {
        if (!$this->_isLoaded() || !$this->_get('hash')) {
            $this->_set('hash', self::generateHash());
        }

        return parent::_save($forceInsert);
    }
}