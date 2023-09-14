<?php

namespace Architekt\Auth;

use Architekt\DB\Entity;
use Architekt\DB\EntityCache;

abstract class User extends Entity
{
    use EntityCache;

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
            $User->_search()->filter('cle', $_COOKIE[self::SESSION_NAME]);
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
            setcookie(self::SESSION_NAME, $this->_get('cle'), strtotime('+ 7 days'), '/');
            $_COOKIE[self::SESSION_NAME] = $this->_get('cle');
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

    public function save(bool $force_insert = false): bool
    {
        if (!$this->_has('cle') || $this->_isNull('cle')) {
            $this->_set('cle', self::generateHash());
        }

        return parent::_save($force_insert);
    }
}