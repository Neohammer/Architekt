<?php

use Architekt\Installer\Application;
use Architekt\Installer\Plugin;

/** @var Plugin $this */
$this;


/** @var Application[] $applicationsConcerned */
$applicationsConcerned = [$this->application];
if ($this->application->isAdmin) {
    $applicationsConcerned = array_merge($applicationsConcerned, $this->project->applicationsWithAdministration($this->application->code));
}

foreach ($applicationsConcerned as $application) {
    $applicationUserClass = $this->architekt->toCamelCase($application->user());
    $code = strtolower($applicationUserClass);

    $template = $this->template()
        ->assign('ParentApplicationControllerName', $code)
        ->assign($application->templateVarsFromApplicationUser());

    $this
        ->directoryCreate(
            $this->application->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass
        )
        ->fileCreate(
            $this->application->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass . DIRECTORY_SEPARATOR . 'IndexController.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'ApplicationUserController.php.tpl'
        );

}

