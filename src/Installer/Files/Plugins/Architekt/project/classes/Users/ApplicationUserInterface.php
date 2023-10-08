<?php

namespace Users;

use Architekt\Auth\Profile;

interface ApplicationUserInterface
{
    public function user(): User;

    public function profile(): Profile;

    /** @return static[] */
    public static function byUser(User $user): array;

    public static function lastByUser(User $user): ?static;

}