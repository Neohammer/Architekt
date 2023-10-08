<?php

namespace Controllers;

use Architekt\Application;
use Architekt\Auth\Access\Attributes\SettingAttribute;
use Architekt\Auth\Access\Attributes\SettingDependencyAttribute;
use Architekt\Controller;
use Architekt\Logger;
use Architekt\Plugin;
use Architekt\Utility\Settings;
use Architekt\Utility\UserApplicationControllerTrait;
use Users\{$APPLICATION_USER_CAMEL} as ApplicationUser;
use Users\User;

abstract class {$APPLICATION_CAMEL}Controller extends \Architekt\Http\Controller
{
    use UserApplicationControllerTrait;

    private ?User $__user = null;
    private ?ApplicationUser $__applicationUser = null;

    abstract public function __plugin(): Plugin;

    abstract public function __controller(): Controller;

    public function __application(): Application
    {
        return Application::fromCache({$APPLICATION->_primary()});
    }

    public function __templateVars(): array
    {
        return [
            'APPLICATION_USER' => $this->__applicationUser,
            'APPLICATION_SETTINGS' => Settings::byApplication($this->__application()),
            'CONTROLLER' => $this,
            // 'NOTIFICATIONS' => InternalNotification::getForCurrentApp()
        ];
    }

    protected function fillMedias(): static
    {
        $this->view
            ->addMediaCss('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap')
            {foreach from=$WEBVENDORS_FILES.stylesheets item=libraryFile}            ->addMediaCss('{$WEBVENDORS_FILES.directory}{$libraryFile}')
            {/foreach}
            {foreach from=$THEME_FILES.stylesheets item=libraryFile}            ->addMediaCss('{$THEME_FILES.directory}{$libraryFile}')
            {/foreach}
            {foreach from=$WEBVENDORS_FILES.javascripts item=libraryFile}            ->addMediaJs('{$WEBVENDORS_FILES.directory}{$libraryFile}')
            {/foreach}
            {foreach from=$THEME_FILES.javascripts item=libraryFile}            ->addMediaJs('{$THEME_FILES.directory}{$libraryFile}')
            {/foreach};

        return $this;
    }

    protected function initUser(): static
    {
        $this->__applicationUser = ApplicationUser::loadFromSession();
        $this->__user = User::loadFromSession();

        return $this;
    }

    public function __user(): ?User
    {
        return $this->__user;
    }

    public function __applicationUser(): ?ApplicationUser
    {
        return $this->__applicationUser;
    }
}