<?php

use Architekt\Controller;
use Architekt\Utility\Settings;

/** @var \Architekt\Installer\Plugin $this */
$this;

$controllers = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'customControllers.json'), true);

$applicationsConcerned = [$this->application];
if ($this->application->isAdmin) {
    $applicationsConcerned = array_merge($applicationsConcerned, $this->project->applicationsWithAdministration($this->application->code));
}

$adminControllers = [
    'Index',
    'Redirect'
];

if ($this->application->isAdmin){
    $adminControllers[] = 'Profile';
}

foreach ($applicationsConcerned as $application) {
    if ($application->hasCustomUser()) {

        foreach ($adminControllers as $adminController) {
            $controller = $controllers['appUser/' . $adminController];
            $controllerCode = ucfirst($application->user()) . '/' . $adminController;
            $controller['name'] = sprintf($controller['name'], ucfirst($application->user()));

            $controllersToInstall[$controllerCode] = $controller;
        }
    }
}

