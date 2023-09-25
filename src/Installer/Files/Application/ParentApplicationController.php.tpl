<?php

namespace Controllers;

use Architekt\Plugin;
use Architekt\DB\Entity;
use Architekt\Http\Controller;
use Architekt\Http\Request;
use Architekt\Logger;
use Architekt\Utility\Settings;
use Attributes;
use Notifications\InternalNotification;
use Users\Administrator;
use Users\Profile;
use Users\User;

abstract class {$APPLICATION_CAMEL}Controller extends Controller
{
    private ?User $__user = null;
    private ?Administrator $__administrator = null;

    abstract public function __plugin(): Plugin;


    protected function hasAccess(string $method): bool
    {
        $settings = $this->__plugin()->settings();

        $hasToBeLoggedAsUser = $settings->method($method)->loggedUser()->hasToBeLogged();
        if($hasToBeLoggedAsUser === true && !$this->__user()){
            Logger::warning(sprintf('%s : require logged user',$method));
            return false;
        }

        $hasToBeLogged = $settings->method($method)->logged()->hasToBeLogged();

        if ($hasToBeLogged && !$this->__administrator()) {
            Logger::warning(sprintf('%s : require logged administrator',$method));
            return false;
        }

        $requiredDependenciesMatch = 0;
        foreach ($settingDependencies = $settings->method($method)->settingDependencies()->get() as $settingDependency) {
            if ($settingDependency->plugin === 'User') {
                if (!$this->__user()) {
                    Logger::warning(sprintf('%s : setting dependencies require logged user',$method));
                    return false;
                }
                if ($this->__user()->profile()->settings()->is($settingDependency->plugin, $settingDependency->code, $settingDependency->value)) {
                    $requiredDependenciesMatch++;
                }
            } else {
                die('unknown management');
            }
        }

        if ($requiredDependenciesMatch !== count($settingDependencies)) {
            Logger::warning(sprintf('%s : setting dependencies do not match %s/%s',$method,$requiredDependenciesMatch, count($settingDependencies)));
            return false;
        }

        if (!$hasToBeLogged) {
            Logger::info(sprintf('%s : not logged require',$method));
            return true;
        }

        if (!$settings->method($method)->accesses()->get()) {
            Logger::info(sprintf('%s : not accesses require',$method));
            return true;
        }

        foreach ($settings->method($method)->accesses()->get() as $accessToCheck) {
            if ($this->__administrator()->profile()->allow($this->__module(), $accessToCheck->code)) {
                Logger::info(sprintf('%s : access found > %s',$method,$accessToCheck->code));
                return true;
            }
        }

        Logger::warning(sprintf('%s : generic fail',$method));
        return false;
    }

    /*
    protected function hasAccess(string $method): bool
    {
        return $this->__administrator() !== null;
    }
*/
    protected function initUser(): static
    {
        $this->__administrator = Administrator::loadFromSession();
        $this->__user = User::loadFromSession();

        return $this;
    }

    public function __user(): ?User
    {
        return $this->__user;
    }

    public function __templateVars(): array
    {
        return [
            'ADMINISTRATOR' => $this->__administrator,
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

        switch (Settings::byApp()->get('modal', 'system')) {
            case "swal2":
                $this->view
                    ->addMediaJs('assets/vendors/sweetalert2/sweetalert2.min')
                    ->addMediaCss('assets/vendors/sweetalert2/sweetalert2.min')
                    ->addMediaJs('modals/sweetalert2');
                break;
            default:
                $this->view
                    ->addMediaJs('modals/bootstrap');
        }

        return $this;
    }

    public function __administrator(): ?Administrator
    {
        return $this->__administrator;
    }

    private function _entityCheck(Entity $entity, ?string $id = null): mixed
    {
        if (null === $id) {
            Request::to403();
        }

        $entity->__construct($id);
        if (!$entity->_isLoaded()) {
            Request::to404();
        }

        return $entity;
    }

    protected function _player(string $primary): User
    {
        return $this->_entityCheck(new User(), $primary);
    }

    protected function _administrator(string $primary): Administrator
    {
        return $this->_entityCheck(new Administrator(), $primary);
    }

    protected function _profile(string $primary): Profile
    {
        return $this->_entityCheck(new Profile(), $primary);
    }

    protected function _plugin(string $primary): Plugin
    {
        return $this->_entityCheck(new Plugin(), $primary);
    }
}