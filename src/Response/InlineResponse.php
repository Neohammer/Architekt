<?php

namespace Architekt\Response;

use Architekt\Http\Request;
use Architekt\View\Message;

class InlineResponse extends BaseResponse
{
    private bool $error;
    private ?string $successMessage;
    private ?string $errorMessage;

    public function __construct()
    {
        $this->error = false;
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function success(?string $message = null): static
    {
        $this->successMessage = $message;

        return $this;
    }

    public function error(string $message): static
    {
        $this->error = true;
        $this->errorMessage = $message;

        return $this;
    }

    public function isSuccess(): bool
    {
        return !$this->error;
    }

    public function send(): void
    {
        if ($this->error) {
            Message::addError($this->errorMessage);
        } elseif ($this->successMessage) {
            Message::addSuccess($this->successMessage);
        }

        $routes = $this->buildRoute();
        $route = $routes['reloadTo'] ?? $routes['returnTo'] ?? null;

        if ($route) {
            Request::redirect($route);
        }
    }
}