<?php

namespace Architekt\Utility;

use Architekt\Application;
use Architekt\Auth\Profile;
use Architekt\Controller;

class Settings
{
    public static function byApplication(?Application $application = null): ApplicationSettings
    {
        return new ApplicationSettings($application ?? Application::get());
    }

    public static function byController(Controller $controller): ControllerSettings
    {
        return new ControllerSettings($controller);
    }

    public static function byProfile(Profile $profile, ?Application $application = null): ProfileSettings
    {
        return new ProfileSettings($profile, $application);
    }
}