<?php

namespace Users;

use Architekt\DB\EntityCache;

class User extends \Architekt\Auth\User
{
    use EntityCache;

    const PLUGIN = 1;

    protected static ?string $_table = 'user';

    protected static string $_labelField = 'email';

    public function profile(): Profile
    {
        return Profile::fromCache($this->_get('profile_id'));
    }

}