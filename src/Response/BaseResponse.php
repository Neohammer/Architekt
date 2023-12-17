<?php

namespace Architekt\Response;

abstract class BaseResponse
{
    protected ?string $reloadRoute;
    protected ?string $redirectRoute;
    protected ?string $redirectTarget;
    protected ?string $redirectType;
    protected ?string $hideBlock;
    protected ?array $args;

    abstract public function send(): void;

    protected function init(?array $args = []): void
    {
        $this->args = $args;
        $this->reloadRoute = null;
        $this->redirectRoute = null;
        $this->redirectTarget = null;
        $this->redirectType = null;
        $this->hideBlock = null;
    }

    public function setReload($route): self
    {
        $this->reloadRoute = $route;
        $this->redirectRoute = null;
        $this->redirectTarget = null;

        return $this;
    }

    public function setRedirect(string $route, ?string $target = null, ?string $type = 'replace'): self
    {
        $this->redirectRoute = $route;
        $this->redirectTarget = $target;
        $this->redirectType = $type;
        $this->reloadRoute = null;

        return $this;
    }

    public function hideBlock(string $blockSelector): self
    {
        $this->hideBlock = $blockSelector;

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
            if (null !== $this->redirectType) {
                $route['returnType'] = $this->redirectType;
            }
        }
        if (null !== $this->reloadRoute) {
            $route['reloadTo'] = $this->reloadRoute;
        }

        if (null !== $this->hideBlock) {
            $route['hideBlock'] = $this->hideBlock;
        }

        return $route;
    }

    public function getArg(string $key): mixed
    {
        if (!is_array($this->args) || !array_key_exists($key, $this->args)) {
            return null;
        }
        return $this->args[$key];
    }

    public function setArg(string $key, mixed $value): static
    {
        $this->args[$key] = $value;

        return $this;
    }

}