<?php

namespace Users;

use Architekt\Auth\Attempt;

class LoginAttempt extends Attempt
{
    protected const QUOTA = 5;
    protected const LOCK_TIME = '+5 minutes';
    protected const VAR_ATTEMPT = 'loginAttempt';
    protected const VAR_LOCK = 'loginLock';

}