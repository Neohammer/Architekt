<?php

namespace Architekt\Auth;

use Architekt\DB\DBEntity;

abstract class Token extends DBEntity
{


    public const USER_CREATE_CONFIRMATION = 'accountCreate';
    public const PASSWORD_RECOVER = 'passwordRecover';
    public const PASSWORD_CHOOSE = 'passwordChoose';

    protected static ?string $_table = 'token';

    public static function get(
        User   $user,
        string $code,
        string $key
    ): ?static
    {
        if (!static::checkKey($key)) {
            return null;
        }

        $that = new static;
        $that
            ->_search()
            ->and($user);

        while ($that->_next()) {
            if ($that->hasExpired()) {
                $that->_delete();
                continue;
            }
            if ($that->key() === $key && $that->_get('code') === $code) {
                return $that;
            }
        }

        return null;
    }

    protected static function build(
        User   $user,
        string $code,
        string $dateTag
    ): static
    {
        $that = new static;
        $that
            ->_set([
                $user,
                'key' => static::generateKey(),
                'code' => $code,
                'datetime' => date('Y-m-d H:i:s', strtotime($dateTag))
            ])
            ->_save();

        return $that;
    }

    protected static function generateKey(): string
    {
        return md5(time() . uniqid());
    }

    protected static function checkKey(string $key): string
    {
        return strlen($key) === 32;
    }

    protected function hasExpired(): bool
    {
        return time() > strtotime($this->_get('datetime'));
    }

    public function key(): string
    {
        return $this->_get('key');
    }
}