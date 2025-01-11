<?php

use Architekt\Controller;
use Architekt\Installer\Command;

$allowApplicationDirectoryCopy = true;

$applicationCamel = $this->architekt->toCamelCase($this->application->code);
$template = $this->template()->assign('APPLICATION_CAMEL', $applicationCamel);


$this
    ->fileCreate(
        $this->project->directoryClassesControllers() . DIRECTORY_SEPARATOR . $applicationCamel . 'Controller.php',
        $template,
        'templates' . DIRECTORY_SEPARATOR . 'ParentApplicationController.php.tpl'
    )
    ->fileCreate(
        $this->project->directoryClasses() . DIRECTORY_SEPARATOR . 'Link'.$applicationCamel . '.php',
        $template,
        'templates' . DIRECTORY_SEPARATOR . 'ApplicationLink.php.tpl'
    )
    ->fileCreate(
        $this->project->directoryClasses() . DIRECTORY_SEPARATOR . 'Link'.$applicationCamel . 'Options.php',
        $template,
        'templates' . DIRECTORY_SEPARATOR . 'ApplicationLinkOptions.php.tpl'
    )
    ->fileCreate(
        $this->project->directoryClasses() . DIRECTORY_SEPARATOR . 'Url'.$applicationCamel . '.php',
        $template,
        'templates' . DIRECTORY_SEPARATOR . 'ApplicationUrl.php.tpl'
    );