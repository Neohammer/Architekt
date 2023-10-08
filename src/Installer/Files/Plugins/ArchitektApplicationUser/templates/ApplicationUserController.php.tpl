<?php

namespace Website\{$APPLICATION_CAMEL}\{$APPLICATION_USER_CAMEL};

use Architekt\Application;
use Architekt\Controller;
use Architekt\Plugin;
use Architekt\Auth\Profile;
use Architekt\Http\Request;
use Architekt\View\Message;
use Controllers\{$APPLICATION_CAMEL}Controller;
use Architekt\Response\ModalResponse;
use Users\{$APPLICATION_USER_CAMEL} as ApplicationUser;
use Users\{$APPLICATION_USER_CAMEL}Constraints as ApplicationUserConstraints;

#[Access('none','Enable by default','No rights action')]
#[Access('viewer','Reader','Allow display')]
#[Access('writer','Writer','Allow modification')]
#[UserLogged]
class IndexController extends {$APPLICATION_CAMEL}Controller
{
    const APPLICATION_USER_CODE = '{$APPLICATION_USER_CAMEL}';
    const APPLICATION_USER_DISPLAY_NAME = '{$APPLICATION_USER_LOW}';
    const APPLICATION_USER_DISPLAY_NAMES = '{$APPLICATION_USER_LOW}s';

{include file='./../../templates/controllerHeader.tpl' name="{$APPLICATION_USER_CAMEL}/Index" uri="{$APPLICATION_USER_CAMEL}"}

    public function __templateVars(): array
    {
        return array_merge(
            parent::__templateVars(),
            [
                'APPLICATION_USER_CODE' => self::APPLICATION_USER_CODE,
                'APPLICATION_USER_NAME' => self::APPLICATION_USER_DISPLAY_NAME,
                'APPLICATION_USER_NAME_FC' => ucfirst(self::APPLICATION_USER_DISPLAY_NAME),
                'APPLICATION_USER_NAMES' => self::APPLICATION_USER_DISPLAY_NAMES,
                'APPLICATION_USER_NAMES_FC' => ucfirst(self::APPLICATION_USER_DISPLAY_NAMES),
                'CONTROLLER_BASE_URI' => $this->__uri(),
                'ALLOW_WRITE' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'writer'),
                'ALLOW_VIEW' => $this->__applicationUser()->profile()->allowController($this->__controller(), 'viewer'),
            ]
        );
    }

    #[Access('none')]
    public function connect(): void
    {
        $accounts = ApplicationUser::byUser($this->__user());

        $applicationUser = current($accounts);
        $applicationUser->sessionRegister();
        Request::redirect();
    }

    #[Unlogged]
    public function autocreate(): void
    {
        $accounts = ApplicationUser::byUser($this->__user());
        if(!$accounts){
            $applicationUser = (new ApplicationUser())
                ->_set([
                    $this->__user(),
                    Profile::default($this->__application())
                ]);

            $applicationUser->_save();
        }
        else{
            $applicationUser = current($accounts);
        }

        $applicationUser->sessionRegister();
        Request::redirect('/home');
    }

    #[AccessUser('multiple')]
    #[Description('Display all your accounts')]
    public function choose(): void
    {
        $accounts = ApplicationUser::byUser($this->__user());

        $isAllowedToCreate = true;//$this->__settings()->is('account','create');

        /*if (!$isAllowedToCreate && count($accounts) === 1) {
            $applicationUser = current($accounts);
            $applicationUser->sessionRegister();
            Request::redirect('/Redirect/');
        }*/

        $this
            ->initView('fullpage')
            ->setHtmlTitle('Choix du compte')
            ->assign([
                'ACCOUNTS' => $accounts,
                'CREATE_ALLOW' => $isAllowedToCreate,
            ])
            ->render();
    }

    #[AccessUser('multiple')]
    #[Description('Switch account')]
    public function use(string $applicationUserPrimary): void
    {
        $applicationUser = new ApplicationUser($applicationUserPrimary);

        if ($this->__user()->_isEqualTo($applicationUser->user())) {
            $applicationUser->sessionRegister(true);
            Message::addSuccess("Connexion réussie");
            Request::redirect($this->__uri('Redirect'));
        }

        Message::addError("Impossible de se connecter sur ce compte");
        Request::redirect($this->__uri('Redirect'));
    }

    #[AccessUser('multiple')]
    #[Description('Create a new account himself')]
    public function createModal(): void
    {
        $profile = new Profile();
        $profile->_search()
            ->and($profile, $this->__application())
            ->and($profile, 'user' , 0)
            ->orderAsc($profile, 'name');

        (new ModalResponse(
            sprintf('Créer un %s', self::APPLICATION_USER_DISPLAY_NAMES),
            $this
            ->initView()
            ->assign('PROFILES', $profile->_results())
            ->addMediaJsInternal('js/administrator')
            ->getHtml()
        ))
        ->form($this->__uri('createModal'))
        ->submitButton('Créer')
        ->send();
    }

    #[AccessUser('multiple')]
    #[Description('Create a new account himself [POST]')]
    public function postCreateModal(): void
    {
        $response = ApplicationUserConstraints::tryCreate(
            $this->__user(),
            Request::post('profile_id')
        );

        if($response->isSuccess()){
            $response->setRedirect($this->__uri('Redirect'));
        }

        $response->send();
    }

    #[Access('viewer')]
    #[Description('Display all')]
    public function list(): void
    {
        $applicationUser = new ApplicationUser();
        $applicationUser->_search();
        $this->initView()
            ->setHtmlTitle(sprintf("Liste des %s",self::APPLICATION_USER_DISPLAY_NAMES))
            ->assign([
                'APPLICATION_USERS' => $applicationUser->_results(),
            ])
            ->render();
    }

    #[Logged]
    #[AccessUser('multiple')]
    public function logout(): void
    {
        ApplicationUser::sessionUnregister();
        if ($this->__user()->profile()->settings()->is('User/Index', 'account', 'multiple')) {
            Request::redirect($this->__uri('choose'));
        }
        Request::redirect('/User/logout');
    }

    #[Access('viewer')]
    #[Description('Display')]
    public function view(string $applicationUserPrimary): void
    {
        $applicationUser = $this->_entityCheck(new ApplicationUser(), $applicationUserPrimary);

        $this->initView()
            ->setHtmlTitle(sprintf('Visualisation %s', $applicationUser->label()))
            ->assign([
                'applicationUser' => $applicationUser,
            ])
            ->render();

    }

    #[Access('writer')]
    #[Description('Create a new account (administration)')]
    public function createByAdministrationModal(): void
    {
        $profile = new Profile();
        $profile->_search()
            ->and($profile, Application::byApplicationUserName(self::APPLICATION_USER_CODE))
            ->and($profile, 'user' , 0)
            ->orderAsc($profile, 'name');

        (new ModalResponse(
            sprintf('Créer un %s', self::APPLICATION_USER_DISPLAY_NAME),
            $this
                ->initView()
                ->assign('PROFILES', $profile->_results())
                ->addMediaJsInternal('js/administrator')
                ->getHtml()
        ))
            ->form($this->__uri('createByAdministrationModal'))
            ->submitButton('Créer')
            ->send();
    }

    #[Access('writer')]
    #[Description('Create a new account (administration) [POST]')]
    public function postCreateByAdministrationModal(): void
    {
        $response = ApplicationUserConstraints::tryCreateByAdministration(
            Request::post('email'),
            Request::post('profile_id'),
            Request::post('create_if_missing', '0'),
            Request::post('password'),
            Request::post('password_send', '0'),
        );

        if ($response->isSuccess()) {
            /** @var ApplicationUser $applicationUser */
            $applicationUser = $response->getArg('applicationUser');
            $response->setRedirect($this->__uri(sprintf('view/%s', $applicationUser->_primary())));
        }

        $response->send();
    }

}