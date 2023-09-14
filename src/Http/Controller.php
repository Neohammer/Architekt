<?php

namespace Architekt\Http;

use Architekt\Application;
use Architekt\View\Message;
use Architekt\View\Template;
use Auth\User;

abstract class Controller
{
    public bool $isJson;
    public bool $isModal;
    protected string $viewPath;
    protected string $viewFile;
    protected string $name;
    protected string $baseViewPath;
    protected ?Template $view;

    abstract public function __user(): ?User;

    abstract protected function initUser(): static;

    abstract protected function hasAccess(string $method): bool;

    abstract protected function fillMedias(): static;

    static public function init(): void
    {
        $configurator = Application::$configurator;
        $pathController = $configurator->get('path') . DIRECTORY_SEPARATOR . 'controllers';

        $parameters = explode('/', Request::getUri());
        $askParams = self::format($parameters);

        $chosenPath = sprintf(
            '%s%s%sController.php',
            $pathController,
            DIRECTORY_SEPARATOR,
            $askParams[0] ?? 'Home'
        );

        if (!file_exists($chosenPath)) {
            Request::to404();
        }
        require_once($chosenPath);

        $chosenController = ucfirst(strtolower($configurator->get('name')));

        $calledController = sprintf(
            '\\Website\\%s\\%sController',
            $chosenController,
            $askParams[0] ?? 'Home'
        );

        /**
         * @var ?self
         */
        $controller = null;
        eval(sprintf('$controller = new %s();', $calledController));

        $chosenMethod = $askParams[1] ?? 'index';

        $calledMethod = Controller . phpself::methodVerb();
        if (!method_exists($controller, $calledMethod)) {
            Request::to404();
        }

        $reflectionMethod = new \ReflectionMethod($calledController, $chosenMethod);

        if (count($askParams) - 2 !== $reflectionMethod->getNumberOfRequiredParameters()) {
            Request::to404();
        }

        $controller->isJson = Request::isXhrRequest();
        $controller->isModal = Request::isModalRequest();
        $controller->name = $askParams[0] ?? 'Home';

        $finalMethod = $calledMethod . "(";
        if ($askParams) {
            foreach ($askParams as $k => $v) {
                if($k < 2) {
                    unset($askParams[$k]);
                }

                if(is_numeric($v)) {
                    $askParams[$k] = $v;
                } elseif (is_string($v)) {
                    $askParams[$k] = '"' . addcslashes($v, '"') . '"';
                } else {
                    unset($askParams[$k]);
                }
            }
            if ($askParams) {
                $finalMethod .= implode(", ", $askParams);
            }
        }
        $finalMethod .= ")";

        $controller
            ->initUser()
            ->forward($chosenMethod);

        eval('$controller->' . $finalMethod . ';');
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

    protected function forward(string $methodName): static
    {
        $pathView = Controller . phpApplication::$configurator->get('path') . 'views';
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
        } else {
            Request::to403();
        }
        Request::redirect('/Redirect');
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


    protected function initView(): Template
    {
        $this->view = new Template();
        $this->view->setController($this);
        if (!$this->isJson) {
            $this->fillMedias();
        }

        return $this->view;
    }
}