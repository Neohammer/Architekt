<?php

namespace Architekt\Http;

use Architekt\Application;
use Architekt\Auth\Profile;
use Architekt\Auth\User;
use Architekt\DB\Interfaces\DBEntityInterface;
use Architekt\Logger;
use Architekt\Plugin;
use Architekt\Utility\ApplicationSettings;
use Architekt\Utility\ControllerSettings;
use Architekt\Utility\Settings;
use Architekt\View\Message;
use Architekt\View\Template;

abstract class Controller
{
    public bool $isJson;
    public bool $isModal;
    protected string $viewPath;
    protected string $viewFile;
    protected string $name;
    protected string $baseViewPath;
    protected ?Template $view;
    protected Controller $__controller;

    abstract public function __user(): ?User;

    abstract protected function initUser(): static;

    abstract protected function fillMedias(): static;


    abstract public function __application(): Application;

    abstract public function __plugin(): Plugin;

    abstract public function __controller(): \Architekt\Controller;

    abstract public function __templateVars(): array;


    protected function hasAccess(string $method): bool
    {
        $settings = $this->__controller()->parse();

        $hasToBeLoggedAsUser = $settings->method($method)->loggedUser()->hasToBeLogged() ?? $settings->loggedUser()->hasToBeLogged();

        if (!$hasToBeLoggedAsUser) {
            Logger::info(sprintf('%s : not logged require', $method));

            return true;
        }

        if (!$this->__user()) {
            Logger::warning(sprintf('%s : require logged user', $method));

            return false;
        }

        if (!$accesses = $settings->method($method)->accessesUser()->get()) {
            Logger::info(sprintf('%s : not user accesses require', $method));

            return true;
        }

        if (!$this->__user()) {
            Logger::warning(sprintf('%s : require logged user to check access', $method));

            return false;
        }

        $profile = $this->__user()->profile();

        $userController = \Architekt\Controller::byApplicationAndNameSystem($this->__application(), 'User/Index');

        foreach ($accesses as $accessToCheck) {
            if ($accessToCheck->code === 'none') {
                return true;
            }
            if ($profile->allowController($userController, $accessToCheck->code)) {
                Logger::info(sprintf('%s : access found > %s', $method, $accessToCheck->code));
                return true;
            }
        }

        Logger::warning(sprintf('%s : generic user fail', $method));

        return false;
    }

    protected function _entityCheck(DBEntityInterface $entity, ?string $id = null): mixed
    {
        if (null === $id) {
            Request::to403();
        }

        $entity->__construct($id);
        if (!$entity->_isLoaded()) {
            Request::to404();
        }

        return $entity;
    }

    private static function __isValidMethod(string $method): bool
    {
        return preg_match('|^[A-Za-z\-_]+$|', $method);
    }

    public static function init(): void
    {
        $configurator = Application::$configurator;
        $pathControllers = $configurator->get('path') . DIRECTORY_SEPARATOR . 'controllers';

        $askParams = self::format(explode('/', Request::getUri()));

        $askParams[0] ??= 'index';
        $askParams[1] ??= 'index';

        $callables = [];

        $dirExists = is_dir($pathControllers . DIRECTORY_SEPARATOR . $askParams[0]);
        $dirIndexExists = is_dir($pathControllers . DIRECTORY_SEPARATOR . 'Index');


        if ($dirExists) {
            if(isset($askParams[2])){
                if(self::__isValidMethod($askParams[2])) {
                    $callables[] = [
                        'class' => ucfirst($askParams[0]) . '\\' . ucfirst($askParams[1]),
                        'method' => $askParams[2],
                        'params' => array_slice($askParams, 3),
                    ];
                }else{
                    $callables[] = [
                        'class' => ucfirst($askParams[0]) . '\\' . ucfirst($askParams[1]),
                        'method' => 'index',
                        'params' => array_slice($askParams, 2),
                    ];
                }
            }

            if(self::__isValidMethod($askParams[1])) {
                $callables[] = [
                    'class' => ucfirst($askParams[0]) . '\\Index',
                    'method' => $askParams[1],
                    'params' => array_slice($askParams, 2),
                ];
            }
        }

        if(self::__isValidMethod($askParams[1])) {
            $callables[] = [
                'class' => ucfirst($askParams[0]),
                'method' => $askParams[1],
                'params' => array_slice($askParams, 2),
            ];
        }

        $callables[] = [
            'class' => ucfirst($askParams[0]),
            'method' => 'index',
            'params' => array_slice($askParams, 1),
        ];

        if ($dirIndexExists) {

            if(self::__isValidMethod($askParams[1])) {
                $callables[] = [
                    'class' => 'Index\\' . $askParams[0],
                    'method' => $askParams[1],
                    'params' => array_slice($askParams, 1),
                ];
            }

            if(self::__isValidMethod($askParams[0])) {
                $callables[] = [
                    'class' => 'Index\\Index',
                    'method' => $askParams[0],
                    'params' => array_slice($askParams, 3),
                ];
            }
        }

        if(self::__isValidMethod($askParams[0])) {
            $callables[] = [
                'class' => 'Index',
                'method' => $askParams[0],
                'params' => array_slice($askParams, 1),
            ];
        }

        $controller = null;
        foreach ($callables as $callable) {
            try {

                $controllerClass = sprintf(
                    'Website\\%s\\%sController',
                    ucfirst(Application::get()->_get('name_system')),
                    $callable['class']
                );

                $controller = new $controllerClass();
                $methodCalled = $callable['method'];
                $methodToCall = self::addVerbToMethod($methodCalled);

                if (!method_exists($controller, $methodToCall)) {
                    $controller = null;
                    continue;
                }


                $controllerName = str_replace('\\', '/', $callable['class']);
                $askParams = $callable['params'];

                break;
            } catch (\Error $e) {
                continue;
            }
        }

        if (!$controller) {
            Request::to404();
        }

        if (count($askParams) < (new \ReflectionMethod($controller, $methodToCall))->getNumberOfRequiredParameters()) {
            Request::to404();
        }

        $controller->isJson = Request::isXhrRequest();
        $controller->isModal = Request::isModalRequest();
        $controller->name = $controllerName;

        $controller
            ->initUser()
            ->forward($methodCalled)
            ->$methodToCall(...$askParams);

    }

    static private function format(array $toFormat): array
    {
        foreach ($toFormat as $k => $v) {
            if (preg_match('|^[a-z0-9\-_]+$|i', $v))
                $toFormat[$k] = $v;
            else
                unset($toFormat[$k]);
        }
        return array_values($toFormat);
    }

    private static function methodVerb(): string
    {
        $verb = strtolower(Request::method());
        if ('get' === $verb) {
            return '';
        }
        return $verb;
    }

    private static function addVerbToMethod(string $method): string
    {
        $verb = self::methodVerb();
        if (!$verb) {
            return $method;
        }

        return $verb . ucfirst($method);
    }

    protected function forward(string $methodName): static
    {
        $pathView = Application::$configurator->get('path') . DIRECTORY_SEPARATOR . 'views';
        if (!$this->hasAccess($methodName)) {
            self::noAccessRedirect();
        }
        return $this
            ->setBaseViewPath($pathView)
            ->setViewPath($pathView . '/' . $this->name . '/')
            ->setViewFile($methodName);
    }


    protected function noAccessRedirect(?string $message = null): void
    {
        if (!$this->isJson) {
            Message::addError($message ?? 'Page non autorisée. Contacter l\'administrateur');
            Request::redirect('/Redirect', true);
        } else {
            Request::to403('/Redirect');
        }
    }

    public function viewPath(): string
    {
        return $this->viewPath;
    }

    protected function setViewPath(string $viewPath): self
    {
        $this->viewPath = $viewPath;
        return $this;
    }

    public function baseViewPath(): string
    {
        return $this->baseViewPath;
    }

    protected function setBaseViewPath(string $viewPath): self
    {
        $this->baseViewPath = $viewPath;
        return $this;
    }

    public function viewFile(): string
    {
        return $this->viewFile;
    }

    protected function setViewFile(string $viewFile): self
    {
        $this->viewFile = $viewFile;
        return $this;
    }


    protected function initView(string $theme = ''): Template
    {
        $this->view = new Template();
        $this->view->setController($this);
        if (!$this->isJson) {
            $this->fillMedias();
        }
        $this->view->theme = $theme;

        return $this->view;
    }

    public function __appSettings(): ApplicationSettings
    {
        return Settings::byApplication()->overload($this->__settings());
    }

    public function __settings(): ControllerSettings
    {
        return Settings::byController($this->__controller());
    }

    protected function _profile(string $primary): Profile
    {
        return $this->_entityCheck(new Profile(), $primary);
    }
}