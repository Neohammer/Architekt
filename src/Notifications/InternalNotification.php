<?php

namespace Architekt\Notifications;

use Architekt\Application;
use Architekt\DB\DBEntity;
use Users\Administrator;
use Users\Player;
use Users\User;

class InternalNotification extends DBEntity
{

    protected static ?string $_table = 'notifications';

    public static function getForCurrentApp(): array
    {
        $that = new static;

        $that->_search()
            ->and($that,'app_target', Application::get())
            ->limit(10)
            ->orderDesc($that);

        return $that->_results();
    }

    public static function build(
        string $appTarget,
        string $code,
        array  $params
    )
    {
        (new static)
            ->_set([
                'app_origin' => Application::get(),
                'app_target' => $appTarget,
                'code' => $code,
                'params' => json_encode($params)
            ])->_save();
    }
/*
    public function administrator(): Administrator
    {
        return Administrator::fromCache($this->params()['administrator'] ?? null);
    }

    public function user(): User
    {
        return User::fromCache($this->params()['user'] ?? null);
    }

    public function player(): Player
    {
        return Player::fromCache($this->params()['player'] ?? null);
    }*/

    public function params(): array
    {
        return json_decode($this->_get('params'), true);
    }
}