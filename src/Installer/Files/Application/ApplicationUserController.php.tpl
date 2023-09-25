<?php

namespace Website\{$PROJECT_CAMEL};

use Architekt\Http\Request;
use Architekt\View\Message;
use Controllers\{$APPLICATION_CAMEL}Controller;
use Architekt\Plugin;
use Response\ModalResponse;
use Users\{$APPLICATION_USER_CAMEL};
use Users\{$APPLICATION_USER_CAMEL}Constraints;
use Users\Profile;


#[Dependencies('User')]
#[Access('none',ADMINISTRATOR_CONTROLLER_ACCESS_NONE_NAME,ADMINISTRATOR_CONTROLLER_ACCESS_NONE_DESCRIPTION)]
#[Access('viewer',ADMINISTRATOR_CONTROLLER_ACCESS_VIEWER_NAME,ADMINISTRATOR_CONTROLLER_ACCESS_VIEWER_DESCRIPTION)]
#[Access('writer','Writer','Allow {$APPLICATION_USER_LOW} modification')]
#[SettingDependency('User','multi')]
#[Setting('test','Que faire de ce truc','choice',['value1'=>'Value1','value2'=>'Value2'],'value1')]
#[Setting('test2','boolselect','bool',false)]
#[Logged]
class {$APPLICATION_USER_CAMEL}Controller extends {$APPLICATION_CAMEL}Controller
{

    public function __plugin(): Plugin
    {
        return Plugin::fromCache(2);
    }

    #[Access('none')]
    #[Unlogged]
    #[LoggedAsUser]
    #[SettingDependency('User','multi',true)]
    #[Description('Display all your {$APPLICATION_USER_LOW} accounts')]
    public function choose(): void
    {
        $accounts = {$APPLICATION_USER_CAMEL}::byUser($this->__user());

        if (count($accounts) === 1) {
            ${$APPLICATION_USER_LOW} = current($accounts);
            ${$APPLICATION_USER_LOW}->sessionRegister();
            Request::redirect('/Redirect/');
        }

        $this
            ->initView('fullpage')
            ->setHtmlTitle('Choix du compte')
            ->assign([
                'ACCOUNTS' => $accounts
            ])
            ->render();
    }

    #[Access('none')]
    #[Unlogged]
    #[LoggedAsUser]
    #[SettingDependency('User','multi',true)]
    #[Description('Switch {$APPLICATION_USER_LOW} account')]
    public function use(string ${$APPLICATION_USER_LOW}Primary): void
    {
        ${$APPLICATION_USER_LOW} = new {$APPLICATION_USER_CAMEL}(${$APPLICATION_USER_LOW}Primary);

        if ($this->__user()->_isEqualTo(${$APPLICATION_USER_LOW}->user())) {
            ${$APPLICATION_USER_LOW}->sessionRegister(true);
            Message::addSuccess("Connexion réussie");
            Request::redirect('/Redirect');
        }

        Message::addError("Impossible de se connecter sur ce compte");
        Request::redirect('/Redirect');
    }

    #[Access('viewer')]
    #[Description('Display all {$APPLICATION_USER_LOW}s')]
    public function list(): void
    {
        ${$APPLICATION_USER_LOW} = new {$APPLICATION_USER_CAMEL}();
        ${$APPLICATION_USER_LOW}->_search();
        $this->initView()
            ->setHtmlTitle("Liste des {$APPLICATION_USER_LOW}s")
            ->assign([
                'ADMINISTRATORS' => ${$APPLICATION_USER_LOW}->_results()
            ])
            ->render();
    }

    #[Access('multi')]
    #[Description('{$APPLICATION_USER_CAMEL} logout')]
    public function logout(): void
    {
        {$APPLICATION_USER_CAMEL}::sessionUnregister();
        Request::redirect('/Redirect');
    }

    #[Access('writer')]
    #[Description('Create a new {$APPLICATION_USER_LOW}')]
    public function createBy{$APPLICATION_USER_CAMEL}Modal(): void
    {
        $profile = new Profile();
        $profile->_search()->orderAsc('name');

        (new ModalResponse(
            'Créer un administrateur',
            $this
                ->initView()
                ->assign('PROFILES', $profile->_results())
                ->addMediaJsInternal('js/{$APPLICATION_USER_LOW}')
                ->getHtml()
        ))
            ->form('/{$APPLICATION_USER_CAMEL}/createBy{$APPLICATION_USER_CAMEL}')
            ->submitButton('Créer')
            ->send();
    }

    #[Access('writer')]
    #[Hide]
    #[Description('Create a new {$APPLICATION_USER_LOW} [POST]')]
    public function postCreateBy{$APPLICATION_USER_CAMEL}(): void
    {
        $response = {$APPLICATION_USER_CAMEL}Constraints::tryCreateBy{$APPLICATION_USER_CAMEL}(
            Request::post('email'),
            Request::post('profile_id'),
            Request::post('create_if_missing', '0'),
            Request::post('password'),
            Request::post('password_send', '0'),
        );
        if ($response->isSuccess()) {
            /** @var {$APPLICATION_USER_CAMEL} ${$APPLICATION_USER_LOW} */
            ${$APPLICATION_USER_LOW} = $response->getArg('{$APPLICATION_USER_LOW}');
            $response->setRedirect(sprintf('/{$APPLICATION_USER_CAMEL}/view/%s', ${$APPLICATION_USER_LOW}->_primary()));
        }

        $response->send();
    }

    #[Access('viewer')]
    #[Description('Display an {$APPLICATION_USER_LOW}')]
    public function view(string ${$APPLICATION_USER_LOW}Primary): void
    {
        ${$APPLICATION_USER_LOW} = $this->_{$APPLICATION_USER_LOW}(${$APPLICATION_USER_LOW}Primary);

        $this->initView()
            ->setHtmlTitle(sprintf('Visualisation administrateur %s',${$APPLICATION_USER_LOW}->label()))
            ->assign('{$APPLICATION_USER_LOW}', ${$APPLICATION_USER_LOW})
            ->render();

    }

}