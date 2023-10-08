<?php
$allowApplicationDirectoryCopy = true;
$directoryFrom = $this->directoryFiles() . 'templates' .DIRECTORY_SEPARATOR. 'internals' . DIRECTORY_SEPARATOR.'AppWebsiteUser';

$outputDirectory = DIRECTORY_SEPARATOR.'Notifications'.DIRECTORY_SEPARATOR.'Internals'.DIRECTORY_SEPARATOR;
$directoryTarget = $this->application->directoryViews() . $outputDirectory;

$applicationsManaged = [];
if ($this->application->isAdmin) {
    $applicationsManaged = $this->project->applicationsWithAdministration($this->application->code);
}

foreach ($applicationsManaged as $applicationManaged) {
    if ($applicationManaged->hasCustomUser()) {
        $appUserName = $applicationManaged->user();

        $this->directoryCreate(
            $directoryAppUser = $directoryTarget  . ucfirst($appUserName)
        );
        $this->directoryRead(
            $directoryFrom,
            DIRECTORY_SEPARATOR.'views'.$outputDirectory.ucfirst($appUserName)
        );
    }
}
die();