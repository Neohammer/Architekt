<?php

namespace Architekt\Utility;

use Architekt\Auth\Access\Attributes\SettingAttribute;
use Architekt\Auth\Access\Attributes\SettingDependencyAttribute;
use Architekt\Logger;

trait UserApplicationControllerTrait
{
    protected function hasAccess(string $method): bool
    {
        if (!parent::hasAccess($method)) {
            return false;
        }

        $settings = $this->__controller()->parse();

        //APP USER
        $hasToBeLogged = $settings->method($method)->logged()->hasToBeLogged();
        if ($hasToBeLogged && !$this->__applicationUser()) {
            Logger::warning(sprintf('%s : require logged application user', $method));
            return false;
        }

        if ($hasToBeLogged === false) {
            Logger::info(sprintf('%s : not logged require', $method));

            return true;
        }

        if (!$accesses = $settings->method($method)->accesses()->get()) {
            Logger::info(sprintf('%s : not accesses require', $method));

            return true;
        }

        if ($accesses && !$this->__applicationUser()) {
            foreach ($accesses as $access) {
                if ($access->code === 'none') {
                    return true;
                }
            }
            Logger::warning(sprintf('%s : require logged application user to check access', $method));

            die('mouarf mouarf');
            return false;
        }

        if (($settingDependencies = $settings->method($method)->settingDependencies()->get()) && !$this->__applicationUser()) {
            Logger::warning(sprintf('%s : require logged application user to check setting dependencies', $method));

            die('mouarf mouarf');
            return false;
        }

        if (($settingAttributes = $settings->method($method)->settings()->get()) && !$this->__applicationUser()) {
            Logger::warning(sprintf('%s : require logged application user to check setting', $method));

            die('mouarf mouarf');
            return false;
        }

        $profile = $this->__applicationUser()->profile();

        $requiredDependenciesMatch = 0;
        /** @var SettingDependencyAttribute $settingDependency */
        foreach ($settingDependencies as $settingDependency) {
            if ($profile->settings()->is($settingDependency->controllerCode, $settingDependency->code, $settingDependency->subCode, $settingDependency->value)) {
                $requiredDependenciesMatch++;
            }
        }

        if ($requiredDependenciesMatch !== count($settingDependencies)) {
            Logger::warning(sprintf('%s : setting dependencies do not match %s/%s', $method, $requiredDependenciesMatch, count($settingDependencies)));
            return false;
        }

        $requiredSettingsMatch = 0;
        /** @var SettingAttribute $setting */
        foreach ($settingAttributes as $setting) {
            if ($profile->settings()->is($this->__controller(), $setting->code, $setting->subCode, $setting->value)) {
                $requiredSettingsMatch++;
            }
        }

        if ($requiredSettingsMatch !== count($settingAttributes)) {
            Logger::warning(sprintf('%s : setting do not match %s/%s', $method, $requiredSettingsMatch, count($settingAttributes)));
            return false;
        }

        foreach ($settings->method($method)->accesses()->get() as $accessToCheck) {
            if ($accessToCheck->code === 'none') {
                return true;
            }
            if ($profile->allowController($this->__controller(), $accessToCheck->code)) {
                Logger::info(sprintf('%s : access found > %s', $method, $accessToCheck->code));
                return true;
            }
        }

        Logger::warning(sprintf('%s : generic fail', $method));

        return false;
    }
}