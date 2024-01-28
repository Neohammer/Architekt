<?php

namespace Architekt\Auth;

use Architekt\DB\DBEntity;

abstract class Token extends DBEntity
{
    protected static ?string $_table = 'token';

    public static function get(
        string $code,
        string $key,
        User   $user = null
    ): ?static
    {
        if (!static::checkKey($key)) {
            return null;
        }

        $search = ($that = new static)->_search();

        if ($user) {
            $search->and($that, $user);
        } else {
            $search
                ->and($that, 'key', $key)
                ->limit();
        }

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
        string $code,
        string $dateTag,
        ?User  $user = null
    ): static
    {
        $that = new static;
        $that
            ->_set([
                'user_id' => $user,
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