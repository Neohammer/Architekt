<?php

namespace Website\{$APPLICATION_CAMEL};

use Architekt\Auth\Access\Attributes;
use Architekt\Http\Request;
use Controllers\{$APPLICATION_CAMEL}Controller;
use Architekt\Plugin;
{if $APPLICATION_USER}use Users\{$APPLICATION_USER_CAMEL} as AppUser;{/if}
use Users\LoginAttempt;
use Users\User;
use Users\UserConstraints;
use Users\UserControllerInterface;
use Users\UserNotification;

#[Access(Attributes::ACCESS_NONE, 'Enable for everyone', 'All elements are active by default')]
{if $APPLICATION_USER}#[Setting('multi', 'Enable multiple accounts on app', 'bool', false)]
{/if}
class UserController extends {$APPLICATION_CAMEL}Controller implements UserControllerInterface
{
    public function __plugin(): Plugin
    {
        return Plugin::fromCache(1);
    }

    protected function hasAccess(string $method): bool
    {
        if ($method === 'logout') {
            return $this->__user() !== null;
        }

        return $this->__user() === null;
    }

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
                $response->setRedirect('/Redirect');
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
            ->setReload('/Redirect')
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
            $response->setReload('/Redirect/');
        } else {
            if (!LoginAttempt::can()) {
                $response->sendMessage();
                $response->setReload('/User/login');
            }
        }

        $response->send();
    }

    #[Access('none')]
    #[Logged]
    #[Description('Logout')]
    public function logout(): void
    {
        UserNotification::pushLogout($this->__user());

        {if $APPLICATION_USER}AppUser::sessionUnregister();{/if}
        User::sessionUnregister();

        Request::redirect('/Redirect');
    }


    #[Access('none')]
    #[Description('Choose password')]
    public function passwordChoose(string $userPrimary, string $tokenKey): void
    {
        $response = UserConstraints::canPasswordChoose($userPrimary, $tokenKey);

        if (!$response->isSuccess()) {
            $response
                ->setRedirect('/User/login')
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
            $response->setReload('/Redirect');
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

        $response->isSuccess() && $response->sendMessage()->setReload('/Redirect');

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
                ->setRedirect('/Redirect')
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
                    ->setReload('/Redirect');
            } else {
                $response->setRedirect('/User/login');
            }
        }

        $response->send();
    }
}