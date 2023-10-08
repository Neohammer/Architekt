<?php

namespace Architekt\Auth;


use Architekt\Controller;
use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;

if (!defined('ARCHITEKT_DATATABLE_PREFIX')) {
    define('ARCHITEKT_DATATABLE_PREFIX', 'at_');
}

class Access extends DBEntity
{
    use DBEntityCache;

    protected static ?string $_table_prefix = ARCHITEKT_DATATABLE_PREFIX;
    protected static ?string $_table = 'access';

    public function profile(): Profile
    {
        return Profile::fromCache($this->_get('profile_id'));
    }

    public static function add(
        Profile    $profile,
        Controller $controller,
        string     $code
    ): void
    {
        (new Access())
            ->_set($profile)
            ->_set($controller)
            ->_set('access', $code)
            ->_save();
    }

    public static function clear(
        Profile    $profile,
        Controller $controller
    ): void
    {
        $that = new static;
        $that->_search()
            ->and($that, $profile)
            ->and($that, $controller);

        while ($that->_next()) {
            $that->_delete();
        }
    }

    public static function has(
        Profile    $profile,
        Controller $controller,
        ?string    $access = null
    ): bool
    {
        ($that = new static)->_search()
            ->and($that, $profile)
            ->and($that, $controller)
            ->limit();

        if ($access) {
            $that->_search()
                ->and($that, 'access', $access);
        }

        return (bool)$that->_resultsCount();
    }
}