<?php

use Architekt\Installer\Application;
use Architekt\Installer\Plugin;

/** @var Plugin $this */
$this;

$allowApplicationDirectoryCopy = false;

/** @var Application[] $applicationsConcerned */
$applicationsConcerned = [$this->application];
if ($this->application->isAdmin) {
    $applicationsConcerned = array_merge($applicationsConcerned, $this->project->applicationsWithAdministration($this->application->code));
}

foreach ($applicationsConcerned as $application) {

    if (!$application->hasCustomUser()) {
        continue;
    }
    $applicationUserClass = $this->architekt->toCamelCase($application->user());
    $code = strtolower($applicationUserClass);

    //$this->installController($code, ['name' => $applicationUserClass]);

    $template = $this->template()
        ->assign('ParentApplicationControllerName', $code)
        ->assign($application->templateVarsFromApplicationUser());



    $this
        ->directoryCreate(
            $this->application->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass
        )
        ->fileCreate(
            $this->project->directoryClassesUsers() . DIRECTORY_SEPARATOR . $applicationUserClass . '.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'ApplicationUser.php.tpl'
        )
        ->fileCreate(
            $this->application->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass . DIRECTORY_SEPARATOR . 'IndexController.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'ApplicationUserController.php.tpl'
        )
        ->fileCreate(
            $this->project->directoryClassesUsers() . DIRECTORY_SEPARATOR . $applicationUserClass . 'Constraints.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'ApplicationUserConstraints.php.tpl'
        )
        ->fileCreate(
            $this->project->directoryClassesEvents() . DIRECTORY_SEPARATOR . $applicationUserClass . 'Event.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'ApplicationUserEvent.php.tpl'
        )
        ->fileCreate(
            $this->application->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass . DIRECTORY_SEPARATOR . 'RedirectController.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'RedirectController.php.tpl'
        )
        ->fileCreate(
            $this->project->directoryClasses() . DIRECTORY_SEPARATOR . $applicationUserClass . 'View.php',
            $template,
            'templates/' . DIRECTORY_SEPARATOR . 'ApplicationUserView.php.tpl'
        );

    $directoryFrom = $this->directoryFiles() . 'templates' . DIRECTORY_SEPARATOR . 'appUser' . DIRECTORY_SEPARATOR;
    $outputDirectory = DIRECTORY_SEPARATOR . $applicationUserClass . DIRECTORY_SEPARATOR;
    $directoryTarget = $this->application->directoryViews() . $outputDirectory;

    $this->directoryCreate(
        $directoryTarget
    );
    $this->directoryCreate(
        $directoryTarget . 'Index'
    );

    $this->directoryRead(
        $directoryFrom,
        DIRECTORY_SEPARATOR . 'views' . $outputDirectory . 'Index'
    );

    if($this->application->isAdmin) {

        $directoryFrom = $this->directoryFiles() . 'templates' . DIRECTORY_SEPARATOR . 'profile' . DIRECTORY_SEPARATOR;
        $outputDirectory = DIRECTORY_SEPARATOR . $applicationUserClass . DIRECTORY_SEPARATOR . 'Profile' . DIRECTORY_SEPARATOR;
        $directoryTarget = $this->application->directoryViews() . $outputDirectory;

        $this
            ->fileCreate(
                $this->application->directoryControllers() . DIRECTORY_SEPARATOR . $applicationUserClass . DIRECTORY_SEPARATOR . 'ProfileController.php',
                $template->assign(['APPLICATION_STRANGER' => $application->entity()]),
                'templates/' . DIRECTORY_SEPARATOR . 'ProfileController.php.tpl'
            )
            ->directoryCreate(
                $directoryTarget
            )
            ->directoryRead(
                $directoryFrom,
                DIRECTORY_SEPARATOR . 'views' . $outputDirectory,
            );

    }
}

