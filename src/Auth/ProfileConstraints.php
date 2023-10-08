<?php

namespace Architekt\Auth;

use Architekt\Application;
use Architekt\Controller;
use Architekt\Form\BaseConstraints;
use Architekt\Form\Validation;
use Architekt\Response\FormResponse;
use Architekt\Transaction;
use Architekt\Utility\Settings;

class ProfileConstraints extends BaseConstraints
{
    public static function tryAdd(
        ?string $name,
        ?string $applicationPrimary,
        ?string $user,
    ): FormResponse
    {
        $validation = new Validation();
        $profile = new Profile();

        $name = self::_autoCheckString($name);
        if (!$name) {
            $validation->addError('name', 'Le nom est obligatoire');
        } else {
            $validation->addSuccess('name', 'Nom valide');
            $profile->_set('name', $name);
        }

        $profile
            ->_set(new Application($applicationPrimary))
            ->_set('user' , (int)$user);

        if ($validation->isSuccess()) {
            $profile->_save();
        }

        return $validation->response(
            'Profil créé',
            'Profil non créé',
            ['profile' => $profile]
        );
    }

    public static function trySave(
        Profile $profile,
        array   $accesses,
        array   $settings,
    ): FormResponse
    {
        $validation = new Validation();

        foreach ($accesses as $controllerPrimary => $accessCodes) {
            Access::clear($profile, $controller = Controller::fromCache($controllerPrimary));

            foreach ($accessCodes as $code => $valid) {

                if (!$valid) continue;

                Access::add(
                    $profile,
                    $controller,
                    $code
                );
            }
        }

        foreach ($settings as $controllerPrimary => $settingCodes) {
            $controller = Controller::fromCache($controllerPrimary);
            $settingsEntity = Settings::byProfile($profile);

            foreach ($settingCodes as $code => $settingsSubCodes) {
                foreach ($settingsSubCodes as $subCode => $values) {
                    if (strlen($values['value']) > 0 && $values['value'] !== $values['default']) {
                        $settingsEntity->setValue($controller, $code, $subCode, $values['value']);
                    } else {
                        $settingsEntity->unsetValue($controller, $code, $subCode);
                    }
                }
            }
            $profile->_save();
        }

        return $validation->response(
            'Accès et configurations changés',
            'Impossible de changer les accès et les configurations'
        );
    }
}