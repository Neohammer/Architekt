<?php

$cors = [];
foreach($this->project->environments() as $environment) {
    foreach ($this->project->applicationsWithCdn($this->application->code) as $application) {
        foreach ($application->urls($environment) as $url) {
            if (!array_key_exists($environment, $cors)) $cors[$environment] = [];
            $cors[$environment] = preg_quote($url);
        }
    }
}

$template = $this->template()->assign('CORS_VALUES', $cors);

foreach ($this->project->environments() as $environment) {
    if (array_key_exists($environment , $cors)) {
        $this->fileCreate(
            sprintf(
                $this->application->directoryWeb() . DIRECTORY_SEPARATOR . '.htaccess%s',
                ($environment === 'local' ? '' : '.' . $environment)
            ),
            $template->assign('CORS_VALUES', $cors[$environment]),
            '.htaccess-cdn.tpl'
        );
    }
}