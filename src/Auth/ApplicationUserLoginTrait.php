<?php

namespace Architekt\Auth;

use Architekt\Http\Request;

trait ApplicationUserLoginTrait
{
    public static function loadFromSession(): ?ApplicationUserInterface
    {
        if ($id = self::retrieveIdFromSession()) {
            $user = new static($id);
            if ($user->_isLoaded()) {
                return $user;
            }
        }

        if ($hash = self::retrieveHashFromCookie()) {
            ($user = new static())
                ->_search()
                ->and($user, 'hash', $hash);

            if ($user->_next()) {
                $user->sessionRegister();
                return $user;
            }
        }

        return null;
    }
}