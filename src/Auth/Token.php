<?php

namespace Architekt\Auth;

use Architekt\DB\Entity;

abstract class Token extends Entity
{
    protected static ?string $_table = 'token';

    public static function get(string|User $userPrimary, string $key): ?static
    {
        if (!static::checkKey($key)) {
            return null;
        }

        $that = new static;
        $that
            ->_search()
            ->filter('key', $key)
            ->filter($userPrimary instanceof User ? $userPrimary : User::fromCache($userPrimary));

        if ($that->_next()) {
            if (!$that->hasExpired()) {

                return $that;
            }
            $that->_delete();
        }

        return null;
    }

    protected static function build(
        User   $user,
        string $dateTag
    ): static
    {
        $that = new static;
        $that
            ->_set([
                $user,
                'key' => static::generateKey(),
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

    public function user(): User
    {
        return User::fromCache($this->_get('user_id'));
    }
}