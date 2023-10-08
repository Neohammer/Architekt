<?php

use Architekt\Controller;
use Architekt\Installer\Command;

$allowApplicationDirectoryCopy = true;

$template = $this->template();

$this
    ->fileCreate(
        $this->project->directoryClassesControllers() . DIRECTORY_SEPARATOR . sprintf('%sController.php', $this->architekt->toCamelCase($this->application->code)),
        $template,
        'templates/ParentApplicationController.php.tpl'
    );