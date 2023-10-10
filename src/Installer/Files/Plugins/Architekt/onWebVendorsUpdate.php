<?php

use Architekt\Installer\Application;
use Architekt\Installer\Plugin;

/** @var Plugin $this */
$this;

$this->fileReplace = true;
$template = $this->template();

$this
    ->fileCreate(
        $this->project->directoryClassesControllers() . DIRECTORY_SEPARATOR . sprintf('%sController.php', $this->architekt->toCamelCase($this->application->code)),
        $template,
        'templates/ParentApplicationController.php.tpl'
    );
$this->fileReplace = false;

