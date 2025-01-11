<?php

namespace Architekt\Auth;

interface ApplicationUserInterface
{
    public function _isLoaded(): bool;

    public function user(): User;

    public function profile(): Profile;

    /** @return static[] */
    public static function byUser(User $user): array;

    public static function lastByUser(User $user): ?static;

}