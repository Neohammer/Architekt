<?php

namespace Architekt\Auth\Access;

use Architekt\Application;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\Http\Controller;

class ControllerParser
{
    public static function attributes(\Architekt\Controller $controller): ClassAttributesParser
    {
        if (!$controller->_isLoaded()) {
            throw new MissingConfigurationException('Controller\'s Plugin does not exists');
        }

        $class = sprintf(
            '%s\%sController',
            Application::controllerNamespace($controller),
            str_replace('/', '\\', ucfirst($controller->_get('name_system')))
        );

        $file = PATH_PROJECT . DIRECTORY_SEPARATOR . '_' . $controller->application()->_get('name_system') . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller->_get('name_system') . 'Controller.php';

        if (!class_exists($class , false)) {
            require_once($file);
        }
        /** @var Controller $controllerObject */
        $controllerObject = new $class();

        return new ClassAttributesParser($controllerObject);
    }
}