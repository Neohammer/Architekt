<?php

namespace Architekt\Auth;

use Architekt\Http\Request;

abstract class Attempt
{

    public static function add(): void
    {
        Request::sessionSet(
            static::VAR_ATTEMPT,
            (int)Request::session(
                static::VAR_ATTEMPT,
                0
            ) + 1
        );

        if (static::hasReachQuota()) {
            static::lock();
        }
    }

    public static function clear(): void
    {
        Request::sessionUnset(static::VAR_ATTEMPT);
        Request::sessionUnset(static::VAR_LOCK);
    }

    protected static function hasReachQuota(): bool
    {
        return (int)Request::session(static::VAR_ATTEMPT, 0) > static::QUOTA;
    }

    public static function left(): int
    {
        return max(static::QUOTA - (int)Request::session(static::VAR_ATTEMPT,0), 0);
    }

    public static function can(): bool
    {
        if(static::left() > 0){
            return true;
        }

        if (!Request::session(static::VAR_LOCK, false)) {
            return true;
        }

        if (static::lockExpire()) {
            static::clear();
            return true;
        }

        return false;
    }

    protected static function lock(): void
    {
        Request::sessionSet(static::VAR_LOCK, strtotime(static::LOCK_TIME));
    }

    protected static function lockExpire(): bool
    {
        return Request::session(static::VAR_LOCK, time() - 1) < time();
    }
}