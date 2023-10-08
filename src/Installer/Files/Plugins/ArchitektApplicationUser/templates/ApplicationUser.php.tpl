<?php

namespace Users;

use Architekt\Auth\Profile;
use Architekt\Auth\UserLoginTrait;
use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;
use Users\ApplicationUserInterface;

class {$APPLICATION_USER_CAMEL} extends DBEntity implements ApplicationUserInterface
{
    use DBEntityCache;
    use UserLoginTrait;

    const SESSION_NAME = '{$APPLICATION_USER_LOW}';

    protected static ?string $_table = '{$APPLICATION_USER_LOW}';

    public function label(): string
    {
        return sprintf('%s (#%s)', $this->profile()->label(), $this->_primary());
    }

    public function user(): User
    {
        return User::fromCache($this->_get('user_id'));
    }

    public function profile(): Profile
    {
        return Profile::fromCache($this->_get('profile_id'));
    }

    /** @return static[] */
    public static function byUser(User $user): array
    {
        $that = new static;
        $that->_search()->and($that, $user);

        return $that->_results();
    }

    public static function lastByUser(User $user): ?static
    {
        $account = current(self::byUser($user));

        return $account !== false ? $account : null;
    }
}