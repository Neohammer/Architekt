<?php

namespace Architekt\Response;

class Action extends BaseResponse
{
    private ?string $message = null;

    public function __construct()
    {
        parent::init([]);
    }

    public function send(): void
    {
        echo json_encode($this->buildRoute());
        exit();
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    protected function buildRoute(): array
    {
        return array_merge(
            parent::buildRoute(),
            $this->buildMessage()
        );
    }

    private function buildMessage(): array
    {
        if (null !== $this->message) {
            return [
                'message' => $this->message,
            ];
        }

        return [];
    }
}