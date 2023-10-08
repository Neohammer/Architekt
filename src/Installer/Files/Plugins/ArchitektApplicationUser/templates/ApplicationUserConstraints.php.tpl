<?php

namespace Users;

use Architekt\Auth\Profile;
use Architekt\Form\BaseConstraints;
use Architekt\Form\Validation;
use Architekt\Response\FormResponse;
use Events\{$APPLICATION_USER_CAMEL}Event as ApplicationUserEvent;
use Users\{$APPLICATION_USER_CAMEL} as ApplicationUser;

class {$APPLICATION_USER_CAMEL}Constraints extends BaseConstraints
{
    public static function tryCreate(
        User $user,
        ?string $profilePrimary
    ): FormResponse
    {
        $validation = new Validation();

        ($applicationUser = new ApplicationUser())
            ->_set($user)
            ->_set(new Profile($profilePrimary))
            ->_save();

        ApplicationUserEvent::onCreate($applicationUser);

        return $validation->response(
            'Compte créé',
            'Impossible de créer le compte',
            ['applicationUser' => $applicationUser]
        );
    }

    public static function tryCreateByAdministration(
        ?string $email,
        ?string $profilePrimary,
        ?string $createIfNotExists,
        ?string $passwordIfNotExists,
        ?string $emailChooseIfNotExists
    ): FormResponse
    {
        $successMessage = 'Le compte a été créé';
        $failMessage = 'Le compte n\'a pas pu être créé';

        $validation = new Validation();

        if (!self::validateEmail($email)) {
            $validation->addError('email', 'Email invalide');

            return $validation->response(
                $successMessage,
                $failMessage
            );
        }

        $profile = new Profile((int)$profilePrimary);
        if (!$profile->_isLoaded()) {
            $validation->addError('profile_id', 'Profil inconnu');

            return $validation->response(
                $successMessage,
                $failMessage
            );
        }

        $applicationUser = (new ApplicationUser())->_set($profile);

        $user = new User();
        $user->_search()->and($user,'email', $email)->limit();


        $sendPasswordChooseNotification = false;

        if (!$user->_next()) {
            if ($createIfNotExists === '1') {

                if (!$password = UserConstraints::verifyPassword($passwordIfNotExists)) {
                    if($emailChooseIfNotExists !== '1'){
                        $validation->addError('password','Mot de passe invalide');

                        return $validation->response(
                            $successMessage,
                            $failMessage
                        );
                    }
                    $password = UserConstraints::generatePassword();
                }

                $user->_set([
                    'email' => $email,
                    'password' => User::encryptPassword($password),
                    'active' => 1
                ])->_save();

                $sendPasswordChooseNotification = $emailChooseIfNotExists === '1';

            } else {
                $validation->addError('email', 'Cet utilisateur n\'existe pas');

                return $validation->response(
                    $successMessage,
                    $failMessage
                );
            }
        }

        $applicationUser->_set($user)->_save();

        ApplicationUserEvent::onCreateByAdministrator($applicationUser);
        if($sendPasswordChooseNotification){
            //UserNotification::pushPasswordChoose($user);
        }

        return $validation->response(
            'Le compte a été créé',
            'Le compte n\'a pas pu être créé',
            ['applicationUser' => $applicationUser]
        );
    }
}