<?php

namespace Architekt\Auth\Access;

use Architekt\Application;
use Architekt\Http\Controller;
use Architekt\Plugin;

class ControllerParser
{
    public static function attributes(Plugin $plugin): ClassAttributesParser
    {
        require_once(Application::controllerFile($plugin->_get('name_system'), $plugin->_get('app')));

        /** @var Controller $controller */
        $controller = eval(sprintf(
            'return new %s\%sController();',
            Application::controllerNamespace($plugin->_get('app')),
            $plugin->_get('name_system')
        ));

        return new ClassAttributesParser($controller);
    }
}