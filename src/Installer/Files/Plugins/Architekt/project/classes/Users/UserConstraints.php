<?php

namespace Users;

use Architekt\Application;
use Architekt\Auth\Profile;
use Architekt\Auth\Token;
use Architekt\Form\BaseConstraints;
use Architekt\Form\Validation;
use Architekt\Response\FormResponse;
use Architekt\Response\InlineResponse;
use Architekt\Utility\Settings;
use Events\UserEvent;

class UserConstraints extends BaseConstraints
{
    public static function verifyPassword(?string $password): ?string
    {
        return self::_autoCheckString($password, '[^ ]{6,20}');
    }

    public static function generatePassword(): string
    {
        return substr(\Architekt\Auth\User::generateHash(), 0, 20);
    }

    protected static function forceLogin(User $user, bool $cookies = false): void
    {
        $user->sessionRegister();
        UserEvent::onLogin($user);
        Attempt::clear();
    }

    protected static function loginSuccessMessage(User $user): string
    {
        return sprintf('Bienvenue %s', $user->label());
    }

    public static function tryCreate(
        ?string $email,
        ?string $password,
    ): FormResponse
    {
        $validation = new Validation();
        $user = new User();

        self::checkUser(
            $user,
            $validation,
            $email,
            $password
        );

        $successMessage = 'Compte créé';

        if ($validation->isSuccess()) {
            $user
                ->_set([
                    'active' => 1,
                    'confirmed' => 1
                ])
                ->_set(Profile::default(Application::get(), true))
                ->_save();

            UserEvent::onCreate($user);

            if (Settings::byApplication()->is('account', 'create_login')) {
                self::forceLogin($user);
                $successMessage = self::loginSuccessMessage($user);
            } else {
                $successMessage = 'Compte créé, vous pouvez vous connecter';
            }
        }

        return $validation->response(
            $successMessage,
            'Votre compte n\'a pas pu être créé'
        );
    }

    public static function tryCreateConfirm(
        string $userPrimary,
        string $key,
    ): InlineResponse
    {
        $response = new InlineResponse();
        $user = new User($userPrimary);

        if (!Settings::byApp()->is('account', 'create')) {
            $response->error('Création de compte fermée, impossible de confirmer la création de votre compte');

            return $response;
        }

        if ($user->_get('confirmed') === '1') {
            $response->success('Compte déjà confirmé');

            if (Settings::byApp()->is('account', 'create_login')) {
                self::forceLogin($user);
                $response->success(self::loginSuccessMessage($user));
            }

            return $response;
        }

        if (!$token = Token::get(Token::USER_CREATE_CONFIRMATION, $key, $user)) {
            $response->error('Clé de confirmation introuvable');

            return $response;
        }

        if (!$user->_isEqualTo($token->user())) {
            $response->error('Clé de confirmation erronée');

            return $response;
        }


        $user
            ->_set('confirmed', '1')
            ->_set('active', '1')
            ->_save();

        $token->_delete();

        UserEvent::onCreateConfirmation($user);

        $response->success('Compte confirmé');

        if (Settings::byApp()->is('account', 'create_login')) {
            self::forceLogin($user);
            $response->success(sprintf('Compte confirmé. %s', self::loginSuccessMessage($user)));
        }

        return $response;
    }


    protected static function checkUser(
        User       $user,
        Validation $validation,
        ?string    $email,
        ?string    $password,
    ): void
    {
        if (!self::validateEmail($email)) {
            $validation->addError('email', 'Email invalide');
        } elseif (!$user->isFieldValueUnique('email', $email)) {
            $validation->addError('email', 'Email existe déjà');
        } else {
            $user->_set('email', $email);
            $validation->addSuccess('email', 'Email valide');
        }

        if ($password = self::verifyPassword($password)) {
            $user->_set('password', User::encryptPassword($password));
            $validation->addSuccess('password', 'Mot de passe valide');
        } else {
            $validation->addError('password', 'Mot de passe invalide');
        }
    }

    public static function tryPasswordChoose(
        string  $userPrimary,
        string  $key,
        ?string $password,
    ): FormResponse
    {
        $validation = new Validation();

        $user = new User($userPrimary);
        if (!$user->_isLoaded()) {
            $validation->addError('password', 'Utilisateur inconnu');
        }

        if (!$token = Token::get(Token::PASSWORD_CHOOSE, $key, $user)) {
            $validation->addError('password', 'Clé de confirmation introuvable');
        }

        if ($password = self::verifyPassword($password)) {
            $validation->addSuccess('password', 'Format du mot de passe valide');
        } else {
            $validation->addError('password', 'Format du mot de passe invalide');
        }

        $successMessage = 'Mot de passe créé, vous pouvez vous connecter';
        if ($validation->isSuccess()) {
            $user->_set('confirmed', '1')
                ->_set('password', User::encryptPassword($password))
                ->_save();
            $token->_delete();

            UserEvent::onPasswordChange($user);

            if (Settings::byApp()->is('account', 'create_login')) {
                self::forceLogin($user);
            }
        } else {
            UserEvent::onPasswordChangeFail($user);
        }

        return $validation->response(
            $successMessage,
            'Impossible de créer votre mot de passe',
            ['user' => $user]
        );
    }

    public static function canPasswordChoose(
        string $userPrimary,
        string $key
    ): InlineResponse
    {
        $response = new InlineResponse();

        $user = new User($userPrimary);
        if (!$user->_isLoaded()) {
            $response->error('Utilisateur inconnu');

            return $response;
        }

        if (!Token::get(Token::PASSWORD_CHOOSE, $key, $user)) {
            $response->error('Clé de confirmation introuvable');

            return $response;
        }

        return $response;
    }

    public static function tryCreateWithToken(
        ?string $email,
        ?string $password,
    ): FormResponse
    {
        $validation = new Validation();
        $user = new User();

        self::checkUser(
            $user,
            $validation,
            $email,
            $password
        );

        if ($validation->isSuccess()) {
            $user->_save();
            UserEvent::onCreateTryWithConfirmation($user);
        }

        return $validation->response(
            'Compte créé, un mail de confirmation vous a été envoyé',
            'Votre compte n\'a pas pu être créé',
            ['user' => $user]
        );
    }


    public static function tryLogin(
        ?string $email,
        ?string $password,
        ?string $useCookies
    ): FormResponse
    {
        $user = new User();
        $validation = new Validation();

        $successMessage = 'Connexion réussie';
        $failMessage = 'Connexion échouée';

        Attempt::add();
        if (!Attempt::can()) {
            $failMessage = 'Trop de tentatives de connexion';
            $validation->addError('loginAttempt', $failMessage);

            return $validation->response(
                $successMessage,
                $failMessage
            );
        }

        if (!self::validateEmail($email)) {
            $validation->addError('email', 'Email invalide');
        } else {
            $user->_set('email', $email);
            $validation->addSuccess('email', 'Email valide');
        }

        if ($password = self::verifyPassword($password)) {
            $validation->addSuccess('password', 'Format du mot de passe valide');
        } else {
            $validation->addError('password', 'Format du mot de passe invalide');
        }

        if ($validation->isSuccess()) {
            $user->_search()->and($user, 'email', $email)->limit(1);

            if (!$user->_next()) {
                $validation->addError('email', $failMessage = 'Couple pseudo/mot de passe inconnu');
            } elseif ($user->_get('password') !== User::encryptPassword($password)) {
                $validation->addError('email', $failMessage = 'Les informations ne correspondent pas');
            } elseif ((int)$user->_get('confirmed') !== 1) {
                $validation->addError('email', $failMessage = 'Vous devez confirmer votre compte avant de pouvoir vous connecter');
            } elseif ((int)$user->_get('active') !== 1) {
                $validation->addError('email', $failMessage = 'Votre compte est désactivé');
            } else {
                self::forceLogin($user, (bool)$useCookies);
            }
        }

        return $validation->response(
            $successMessage,
            $failMessage,
            ['user' => $user]
        );
    }

    public static function tryPasswordRecoverApply(
        ?string $email
    ): FormResponse
    {
        $user = new User();
        $validation = new Validation();
        $exists = false;

        if (self::validateEmail($email)) {
            $user->_set('email', $email);
            $validation->addSuccess('email', 'Email valide');
        } else {
            $validation->addError('email', 'Email invalide');
        }

        if ($validation->isSuccess()) {
            $user->_search()->and($user, 'email', $email);

            if ($user->_next()) {
                $exists = true;
                UserEvent::onPasswordTryRecover($user);

            }
        }

        return $validation->response(
            'Vous aller recevoir un lien de réinitialisation de votre mot de passe par mail',
            'Impossible d\'envoyer le mail de réinitialisation',
            ['user' => $user, 'exists' => $exists]
        );
    }

    public static function canPasswordRecoverConfirm(
        string $userPrimary,
        string $key
    ): InlineResponse
    {
        $response = new InlineResponse();

        $user = new User($userPrimary);
        if (!$user->_isLoaded()) {
            $response->error('Utilisateur inconnu');

            return $response;
        }

        if (!Token::get(Token::PASSWORD_RECOVER, $key, $user)) {
            $response->error('Clé de confirmation introuvable');
        }

        return $response;
    }

    public static function tryPasswordRecoverConfirm(
        string  $userPrimary,
        string  $key,
        ?string $password,
    ): FormResponse
    {
        $validation = new Validation();
        $user = new User($userPrimary);;
        if (!$token = Token::get(Token::PASSWORD_RECOVER, $key, $user)) {
            $validation->addError('password', 'Clé de confirmation introuvable');
        }

        if ($password = self::verifyPassword($password)) {
            $validation->addSuccess('password', 'Format du mot de passe valide');
        } else {
            $validation->addError('password', 'Format du mot de passe invalide');
        }

        $messageSuccess = 'Votre mot de passe a été modifié, vous pouvez vous connecter';
        if ($validation->isSuccess()) {
            $user->_set('password', User::encryptPassword($password))->_save();
            $token->_delete();
            UserEvent::onPasswordRecover($user);

            if (Settings::byApp()->is('account', 'create_login')) {
                self::forceLogin($user);
                $messageSuccess = sprintf('Votre mot de passe a été modifié. %s', self::loginSuccessMessage($user));
            }
        }

        return $validation->response(
            $messageSuccess,
            'Impossible de modifier votre mot de passe',
            ['user' => $user]
        );
    }
}