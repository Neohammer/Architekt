<?php

use Architekt\Application;
use Architekt\Auth\Access;
use Architekt\Auth\Profile;
use Architekt\Controller;
use Architekt\DB\DBConnexion;

/** @var \Architekt\Installer\Plugin $this */
$this;


DBConnexion::get()->transactionStart();

$profileUser = Profile::default($this->application->entity(), true);
if (!$profileUser) {
    ($profileUser = new Profile())
        ->_set([
            $this->application->entity(),
            'name' => 'Basic User',
            'default' => 1,
            'user' => 1
        ])
        ->_save();
}

$profile = Profile::default($this->application->entity());

if (!$profile) {
    ($profile = new Profile())
        ->_set([
            $this->application->entity(),
            'name' => sprintf('Basic %s', $this->application->user()),
            'default' => 1,
            'user' => 0
        ])
        ->_save();
}

if($this->application->isAdmin){

    (new Access())
        ->_set([
            $profile,
            Controller::byApplicationAndNameSystem($this->application->entity(), $this->application->user() . '/Index'),
            'access' => 'multiple'
        ])
        ->_save();


    $applications = $this->project->applicationsWithAdministration($this->application->code);
    $applications[] = $this->application;

    foreach($applications as $application){
        $controllerIndex = Controller::byApplicationAndNameSystem($this->application->entity(), ucfirst($application->user()) . '/Index');

        (new Access())
            ->_set([
                $profile,
                $controllerIndex,
                'access' => 'viewer'
            ])
            ->_save();
        (new Access())
            ->_set([
                $profile,
                $controllerIndex,
                'access' => 'writer'
            ])
            ->_save();

        $controllerProfile = Controller::byApplicationAndNameSystem($this->application->entity(), ucfirst($application->user()) . '/Profile');

        (new Access())
            ->_set([
                $profile,
                $controllerProfile,
                'access' => 'viewer'
            ])
            ->_save();
        (new Access())
            ->_set([
                $profile,
                $controllerProfile,
                'access' => 'writer'
            ])
            ->_save();
    }
}

DBConnexion::get()->transactionCommit();