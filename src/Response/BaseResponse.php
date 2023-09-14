<?php

namespace Architekt\Response;

abstract class BaseResponse
{
    protected ?string $reloadRoute;
    protected ?string $redirectRoute;
    protected ?string $redirectTarget;
    protected ?array $args;

    abstract public function send(): void;

    protected function init(?array $args = []): void
    {
        $this->args = $args;
        $this->reloadRoute = null;
        $this->redirectRoute = null;
        $this->redirectTarget = null;
    }

    public function setReload($route): self
    {
        $this->reloadRoute = $route;
        $this->redirectRoute = null;
        $this->redirectTarget = null;

        return $this;
    }

    public function setRedirect(string $route, ?string $target = null): self
    {
        $this->redirectRoute = $route;
        $this->redirectTarget = $target;
        $this->reloadRoute = null;

        return $this;
    }

    protected function buildRoute(): array
    {
        $route = [];
        if (null !== $this->redirectRoute) {
            $route['returnTo'] = $this->redirectRoute;
            if (null !== $this->redirectTarget) {
                $route['returnTarget'] = $this->redirectTarget;
            }
        }
        if (null !== $this->reloadRoute) {
            $route['reloadTo'] = $this->reloadRoute;
        }
        return $route;
    }

    public function getArg($key): mixed
    {
        if (!is_array($this->args) || !array_key_exists($key, $this->args)) {
            return null;
        }
        return $this->args[$key];
    }

}