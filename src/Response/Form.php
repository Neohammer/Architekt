<?php

namespace Architekt\Response;

use Architekt\Form\Validation;

class Form extends BaseResponse
{
    private Validation $validation;
    private ?string $successMessage;
    private ?string $failMessage;

    public function __construct(
        Validation $validation,
        ?string    $successMessage,
        ?string    $failMessage,
        ?array     $args = null
    )
    {
        $this->validation = $validation;
        $this->successMessage = $successMessage;
        $this->failMessage = $failMessage;
        parent::init($args);
    }

    public function isSuccess(): bool
    {
        return $this->validation->isSuccess();
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
                'details' => $this->validation->buildErrorsDetails()
            ],
            parent::buildRoute(),
            $this->buildMessage()
        );
    }

    private function buildMessage(): array
    {
        $message = $this->isSuccess() ? $this->successMessage : $this->failMessage;

        if (null !== $message) {
            return [
                'message' => $message,
            ];
        }
        return [];
    }

    public function successMessage(): string
    {
        return $this->successMessage;
    }

    public function failMessage(): string
    {
        return $this->failMessage;
    }
}