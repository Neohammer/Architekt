<?php

namespace Users;

use Architekt\Http\Request;

class LoginAttempt
{
    private const QUOTA = 5;
    private const LOCK_TIME = '+5 minutes';
    private const VAR_ATTEMPT = 'loginAttempt';
    private const VAR_LOCK = 'loginLock';

    public static function add(): void
    {
        Request::sessionSet(
            self::VAR_ATTEMPT,
            (int)Request::session(
                self::VAR_ATTEMPT,
                0
            ) + 1
        );

        if (self::hasReachQuota()) {
            self::lock();
        }
    }

    public static function clear(): void
    {
        Request::sessionUnset(self::VAR_ATTEMPT);
        Request::sessionUnset(self::VAR_LOCK);
    }

    private static function hasReachQuota(): bool
    {
        return (int)Request::session(self::VAR_ATTEMPT, 0) > self::QUOTA;
    }

    public static function left(): int
    {
        return max(self::QUOTA - (int)Request::session(self::VAR_ATTEMPT,0), 0);
    }

    public static function can(): bool
    {
        if(self::left() > 0){
            return true;
        }

        if (!Request::session(self::VAR_LOCK, false)) {
            return true;
        }

        if (self::lockExpire()) {
            self::clear();
            return true;
        }

        return false;
    }

    private static function lock(): void
    {
        Request::sessionSet(self::VAR_LOCK, strtotime(self::LOCK_TIME));
    }

    private static function lockExpire(): bool
    {
        return Request::session(self::VAR_LOCK, time() - 1) < time();
    }
}