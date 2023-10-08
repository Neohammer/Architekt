<?php

namespace Website\{$APPLICATION_CAMEL}\User;

use Architekt\Controller;
use Architekt\Plugin;
use Architekt\Auth\Access\Attributes;
use Architekt\Http\Request;
use Controllers\{$APPLICATION_CAMEL}Controller;
use Controllers\UserControllerInterface;
use Users\{$APPLICATION_USER_CAMEL} as AppUser;
use Users\LoginAttempt;
use Users\User;
use Users\UserConstraints;
use Events\UserEvent;

#[Access(Attributes::ACCESS_NONE, 'Enable for everyone', 'All elements are active by default')]
#[Setting('account', 'create', 'Authorize account creation', 'bool', false)]
#[Setting('account', 'create_confirm', 'Account creation need email confirmation', 'bool', false)]
#[Setting('account', 'create_login', 'Automatic login after creation or confirmation', 'bool', false)]
#[SettingProfile('account', 'multi', 'Enable multiple accounts on app', 'bool', false)]
class IndexController extends {$APPLICATION_CAMEL}Controller implements UserControllerInterface
{

{include file='./../../../../templates/controllerHeader.tpl' name='User/Index' uri='User'}

    #[Access('none')]
    #[Description('Create a new user')]
    public function create(): void
    {
        if (!$this->__appSettings()->is('account', 'create')) {
            $this
                ->forward('createClosed')
                ->createClosed();
        } else {
            $this
                ->initView('fullpage')
                ->setHtmlTitle("Créer un compte")
                ->assign([
                    'CREATE_ALLOW' => LoginAttempt::can()
                ])
                ->render();
        }
    }

    #[Access('none')]
    #[Description('Create a new user closed')]
    public function createClosed(): void
    {
        if ($this->__appSettings()->is('account', 'create')) {
            $this
                ->forward('create')
                ->create();
        } else {
            $this->initView('fullpage')
                ->setHtmlTitle('Création de compte fermée')
                ->render();
        }
    }

    #[Access('none')]
    #[Display('none')]
    #[Description('Create a new user [POST]')]
    public function postCreate(): void
    {
        if (!$this->__appSettings()->is('account', 'create')) {
            Request::to403('/Redirect');
        }

        if ($this->__appSettings()->is('account', 'create_confirm')) {
            $response = UserConstraints::tryCreateWithToken(
                Request::post('email'),
                Request::post('password')
            );

            if ($response->isSuccess()) {
                $response->setRedirect($this->__uri('Redirect'));
            }
        } else {
            $response = UserConstraints::tryCreate(
                Request::post('email'),
                Request::post('password')
            );

            if ($response->isSuccess()) {
                $response->sendMessage();
                $response->setReload('/Redirect');
            }
        }

        $response->send();
    }

    #[Access('none')]
    #[Description('Confirmation de la création du compte')]
    public function createConfirm(string $userPrimary, string $key): void
    {
        UserConstraints::tryCreateConfirm(
            $userPrimary,
            $key
        )
            ->setReload($this->__uri('Redirect'))
            ->send();
    }

    #[Access('none')]
    #[Description('Login')]
    public function login(): void
    {
        $this
            ->initView('fullpage')
            ->setHtmlTitle("Se connecter")
            ->assign([
                'LOGIN_ALLOW' => LoginAttempt::can()
            ])
            ->render();
    }

    #[Access('none')]
    #[Display('none')]
    #[Description('Login [POST]')]
    public function postLogin(): void
    {
        $response = UserConstraints::tryLogin(
            Request::post('email'),
            Request::post('password'),
            Request::post('stayLogged', '0')
        );

        if ($response->isSuccess()) {
            $response->sendMessage();
            $response->setReload($this->__uri('Redirect'));
        } else {
            if (!LoginAttempt::can()) {
                $response->sendMessage();
                $response->setReload($this->__uri('login'));
            }
        }

        $response->send();
    }

    #[Access('none')]
    #[UserLogged]
    #[Description('Logout')]
    public function logout(): void
    {
        UserEvent::onLogout($this->__user());

        AppUser::sessionUnregister();
        User::sessionUnregister();

        Request::redirect($this->__uri('Redirect'));
    }


    #[Access('none')]
    #[Description('Choose password')]
    public function passwordChoose(string $userPrimary, string $tokenKey): void
    {
        $response = UserConstraints::canPasswordChoose($userPrimary, $tokenKey);

        if (!$response->isSuccess()) {
            $response
                ->setRedirect($this->__uri('login'))
                ->send();
        }

        $this->initView('fullpage')
            ->setHtmlTitle("Choix de votre mot de passe")
            ->render();
    }

    #[Access('none')]
    #[Display('none')]
    #[Description('Choose password [POST]')]
    public function postPasswordChoose(string $userPrimary, string $tokenKey): void
    {
        $response = UserConstraints::tryPasswordChoose(
            $userPrimary,
            $tokenKey,
            Request::post('newPassword')
        );

        if ($response->isSuccess()) {
            $response->setReload($this->__uri('Redirect'));
        }

        $response->send();
    }

    #[Access('none')]
    #[Description('Recover password')]
    public function passwordRecover(): void
    {
        $this->initView('fullpage')
            ->setHtmlTitle('Récupérer son mot de passe')
            ->render();
    }

    #[Access('none')]
    #[Display('none')]
    #[Description('Recover password [POST]')]
    public function postPasswordRecover(): void
    {
        $response = UserConstraints::tryPasswordRecoverApply(Request::post('email'));

        $response->isSuccess() && $response->sendMessage()->setReload($this->__uri('Redirect'));

        $response->send();
    }

    #[Access('none')]
    #[Description('Recover password confirmation')]
    public function passwordRecoverConfirm(string $userPrimary, string $tokenKey): void
    {
        $response = UserConstraints::canPasswordRecoverConfirm(
            $userPrimary,
            $tokenKey
        );

        if (!$response->isSuccess()) {
            $response
                ->setRedirect($this->__uri('Redirect'))
                ->send();
        }

        $this->initView('fullpage')
            ->setHtmlTitle("Modification de votre mot de passe")
            ->render();
    }

    #[Access('none')]
    #[Display('none')]
    #[Description('Recover password confirmation [POST]')]
    public function postPasswordRecoverConfirm(string $userPrimary, string $hash): void
    {
        $response = UserConstraints::tryPasswordRecoverConfirm(
            $userPrimary,
            $hash,
            Request::post('newPassword')
        );

        if ($response->isSuccess()) {
            if ($this->initUser()->__user()) {
                $response
                    ->sendMessage()
                    ->setReload($this->__uri('Redirect'));
            } else {
                $response->setRedirect($this->__uri('login'));
            }
        }

        $response->send();
    }
}