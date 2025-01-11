<?php

namespace Users;

use Architekt\DB\DBEntityCache;

if (!defined('ARCHITEKT_DATATABLE_PREFIX')) {
    define('ARCHITEKT_DATATABLE_PREFIX', 'at_');
}

class User extends \Architekt\Auth\User
{
    use DBEntityCache;

    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'user';

    protected static string $_labelField = 'email';

    protected const SESSION_NAME = 'User';

    protected const COOKIE_LIFETIME = '+ 7 days';

    public function profile(): Profile
    {
        return Profile::fromCache($this->_get('profile_id'));
    }

}