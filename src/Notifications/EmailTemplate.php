<?php

namespace Architekt\Notifications;

use Architekt\Application;
use Smarty;

class EmailTemplate extends Smarty
{
    public function init(): self
    {
        $this->setTemplateDir(Application::$configurator->get('path') . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'Notifications' . DIRECTORY_SEPARATOR . 'Emails')
            ->setCompileDir(PATH_FILER . 'Smarty/compile/');

        return $this;
    }
}