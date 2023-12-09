<?php

namespace Architekt\Response;

use Architekt\Form\Validation;
use Architekt\View\Message;

class FormResponse extends BaseResponse
{
    public function __construct(
        public Validation $validation,
        private ?string   $successMessage,
        private ?string   $failMessage,
        ?array            $args = null
    )
    {
        parent::init($args);
    }

    public function isSuccess(): bool
    {
        return $this->validation->isSuccess();
    }

    public function hasWarnings(): bool
    {
        return $this->validation->hasWarnings();
    }

    public function send(): void
    {
        echo json_encode($this->buildRoute());
        exit();
    }

    protected function buildRoute(): array
    {
        return array_merge(
            [
                'success' => $this->isSuccess(),
                'warning' => $this->hasWarnings(),
                'details' => $this->validation->buildDetails()
            ],
            parent::buildRoute(),
            $this->buildMessage()
        );
    }

    private function buildMessage(): array
    {
        return [
            'message' => $this->message(),
        ];
    }

    public function successMessage(): string
    {
        return $this->successMessage;
    }

    public function failMessage(): string
    {
        return $this->failMessage;
    }

    public function message(): ?string
    {
        return $this->isSuccess() ? $this->successMessage() : $this->failMessage();
    }

    public function sendMessage(): static
    {
        if ($this->isSuccess()) {
            Message::addSuccess($this->message());
        } else {
            Message::addError($this->message());
        }

        return $this;
    }
}