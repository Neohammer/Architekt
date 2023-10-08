<?php

namespace Website\{$APPLICATION_CAMEL}\{$APPLICATION_USER_CAMEL};

use Architekt\Application;
use Architekt\Auth\Profile;
use Architekt\Auth\ProfileConstraints;
use Architekt\Controller;
use Architekt\Http\Request;
use Architekt\Plugin;
use Architekt\Response\ModalResponse;
use Architekt\Utility\Settings;
use Architekt\View\Message;
use Controllers\{$APPLICATION_CAMEL}Controller;

#[Access('viewer', 'Reader', 'Allow profile display')]
#[Access('writer', 'Writer', 'Allow profile modification')]
#[Logged]
class ProfileController extends {$APPLICATION_CAMEL}Controller
{
    const ADMINISTRATED_APPLICATION = {$APPLICATION_STRANGER->_primary()};

{include file='./../../templates/controllerHeader.tpl' name="{$APPLICATION_USER_CAMEL}/Profile" uri="{$APPLICATION_USER_CAMEL}/Profile"}
    #[Access('viewer')]
    #[Description('Display list of profile')]
    public function list(): void
    {
        $profile = new Profile();
        $profile->_search()
            ->and($profile, 'application_id', self::ADMINISTRATED_APPLICATION)
            ->orderAsc($profile, 'name');

        $this->initView()
            ->setHtmlTitle("Profils")
            ->assign([
                'PROFILES' => $profile->_results(),
                'ALLOW_WRITE' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'writer'),
                'ALLOW_VIEW' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'viewer')
            ])
            ->render();
    }

    #[Access('writer')]
    #[Hide]
    #[Description('Set a profil to default (modal)')]
    public function defaultModal(string $profilePrimary): void
    {
        $profile = $this->_profile($profilePrimary);

        (new ModalResponse(
            'Mettre par défaut',
            'Les comptes seront créés avec ce profil par défaut.'
        ))
            ->confirmButton($this->__uri(sprintf('default/%s', $profile->_primary())), 'Par défaut')
            ->send();
    }

    #[Access('writer')]
    #[Description('Set a profil to default')]
    public function default(string $profilePrimary): void
    {
        $profile = $this->_profile($profilePrimary);

        if ($default = Profile::default($profile->application(),(bool)$profile->_get('user'))) {
            $default->_set('default', 0)->_save();
        }
        $profile->_set('default', 1)->_save();

        Message::addSuccess('Profil défini par défaut');
        Request::redirect($this->__uri('list'));
    }

    #[Access('writer')]
    #[Description('Add profile')]
    public function addModal(): void
    {
        (new ModalResponse(
            'Ajouter un profil',
            $this
                ->initView()
                ->assign('APPLICATION_CONCERNED', Application::fromCache(self::ADMINISTRATED_APPLICATION))
                ->getHtml()
        ))
            ->form($this->__uri('add'))
            ->submitButton('Ajouter')
            ->send();

    }

    #[Access('writer')]
    #[Display('none')]
    #[Description('Add profile [POST]')]
    public function postAdd(): void
    {
        $response = ProfileConstraints::tryAdd(
            Request::post('name'),
            self::ADMINISTRATED_APPLICATION,
            Request::post('user'),
        );

        if ($response->isSuccess()) {
            /** @var Profile $profile */
            $profile = $response->getArg('profile');
            $response->setRedirect($this->__uri(sprintf('edit/%s', $profile->_primary())));
        }

        $response->send();
    }

    #[Access('viewer')]
    #[Access('writer')]
    #[Description('View profile')]
    public function view(string $profilePrimary): void
    {
        $profile = $this->_profile($profilePrimary);

        $controller = new Controller();
        $controller->_search()->and($controller, $profile->application())->orderAsc($controller,'name');

        $controllers = $controller->_results();

        $configuration = [];
        $accesses = [];

        /** @var Controller $controller */
        foreach ($controllers as $controller) {
            $parse = $controller->parse();

            $configuration[$controller->_get('name_system')] = [
                'controller' => $controller,
                'accesses' => $parse->accesses()->get(),
                'dependencies' => $parse->dependencies()->get(),
                'logged' => $parse->logged()->hasToBeLogged(),
                'loggedUser' => $parse->loggedUser()->hasToBeLogged(),
                'settings' => $parse->settings()->get(),
                'settingsCurrent' => Settings::byController($controller),
                'settingDependencies' => $parse->settingDependencies()->get(),
                'methods' => [],
                'methodsByAccesses' => [],
            ];

            foreach ($parse->methods() as $method) {

                $accesses = $parse->method($method)->accesses()->get();
                if(!$accesses){
                    continue;
                }
                foreach($accesses as $access){
                    $configuration[$controller->_get('name_system')]['methodsByAccesses'][$access->code][] = $method;
                }

                $configuration[$controller->_get('name_system')]['methods'][$method] = [
                    'description' => $parse->method($method)->description(),
                    'accesses' => $parse->method($method)->accesses()->get(),
                    'dependencies' => $parse->method($method)->dependencies()->get(),
                    'logged' => $parse->method($method)->logged()->hasToBeLogged(),
                    'loggedUser' => $parse->method($method)->loggedUser()->hasToBeLogged(),
                    'settings' => $parse->method($method)->settings()->get(),
                    'settingDependencies' => $parse->method($method)->settingDependencies()->get(),
                ];

            }

        }

        $this->initView()
            ->setHtmlTitle(sprintf('Visualisation profil %s', $profile->label()))
            ->assign('profile', $profile)
            ->assign('accesses', $accesses)
            ->assign('configuration', $configuration)
            ->assign([
                'ALLOW_WRITE' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'writer'),
                'ALLOW_VIEW' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'viewer')
            ])
            ->render();

    }

    #[Access('writer')]
    #[Description('Edit profile')]
    public function edit(string $profilePrimary): void
    {
        $profile = $this->_profile($profilePrimary);

        $controller = new Controller();
        $controller->_search()->and($controller, $profile->application())->orderAsc($controller,'name');

        $controllers = $controller->_results();

        $configuration = [];
        $accesses = [];

        /** @var Controller $controller */
        foreach ($controllers as $controller) {
            $parse = $controller->parse();

            $configuration[$controller->_get('name_system')] = [
                'controller' => $controller,
                'accesses' => $parse->accesses()->get(),
                'dependencies' => $parse->dependencies()->get(),
                'logged' => $parse->logged()->hasToBeLogged(),
                'loggedUser' => $parse->loggedUser()->hasToBeLogged(),
                'settings' => $parse->settings()->get(),
                'settingsCurrent' => Settings::byController($controller),
                'settingDependencies' => $parse->settingDependencies()->get(),
                'methods' => [],
                'methodsByAccesses' => [],
            ];

            foreach ($parse->methods() as $method) {

                $accesses = $parse->method($method)->accesses()->get();
                if(!$accesses){
                    continue;
                }
                foreach($accesses as $access){
                    $configuration[$controller->_get('name_system')]['methodsByAccesses'][$access->code][] = $method;
                }

                $configuration[$controller->_get('name_system')]['methods'][$method] = [
                    'description' => $parse->method($method)->description(),
                    'accesses' => $parse->method($method)->accesses()->get(),
                    'dependencies' => $parse->method($method)->dependencies()->get(),
                    'logged' => $parse->method($method)->logged()->hasToBeLogged(),
                    'loggedUser' => $parse->method($method)->loggedUser()->hasToBeLogged(),
                    'settings' => $parse->method($method)->settings()->get(),
                    'settingDependencies' => $parse->method($method)->settingDependencies()->get(),
                ];

            }

        }

        $this->initView()
            ->setHtmlTitle(sprintf('Visualisation profil %s', $profile->label()))
            ->assign('profile', $profile)
            ->assign('accesses', $accesses)
            ->assign('configuration', $configuration)
            ->assign([
                'ALLOW_WRITE' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'writer'),
                'ALLOW_VIEW' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'viewer')
            ])
            ->render();

    }

    #[Access('writer')]
    #[Description('Edit profile')]
    public function postEdit(string $profilePrimary): void
    {
        $profile = $this->_profile($profilePrimary);

        $response = ProfileConstraints::trySave(
            $profile,
            Request::postArray('access'),
            Request::postArray('settings')
        );

        if ($response->isSuccess()) {
            $response->setRedirect($this->__uri('list'));
        }

        $response->send();
    }

}