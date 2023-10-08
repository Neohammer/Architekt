<?php

namespace Users;

use Architekt\DB\DBEntityCache;

class User extends \Architekt\Auth\User
{
    use DBEntityCache;

    protected static ?string $_table = 'user';

    protected static string $_labelField = 'email';

    protected const SESSION_NAME = 'User';

    protected const COOKIE_LIFETIME = '+ 7 days';

}