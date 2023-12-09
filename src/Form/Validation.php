<?php

namespace Architekt\Form;

use Architekt\Response\FormResponse as ResponseForm;
use Architekt\Transaction;

class Validation
{
    private array $errors;
    private array $successes;
    private array $warnings;

    private static function cleanField(string $field)
    {
        $replacers = [
            '][' => '-',
            '[' => '',
            ']' => ''
        ];

        return str_replace(array_keys($replacers), $replacers, $field);
    }

    public function __construct()
    {
        Transaction::start();
        $this->errors = [];
        $this->successes = [];
        $this->warnings = [];
    }

    public function addResponse(ResponseForm $formResponse): static
    {
        foreach ($formResponse->validation->errors as $error) {
            $this->errors[] = $error;
        }
        foreach ($formResponse->validation->successes as $success) {
            $this->successes[] = $success;
        }

        return $this;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[] = [
            'field' => self::cleanField($field),
            'message' => $message
        ];
    }

    public function isSuccess(): bool
    {
        return !sizeof($this->errors) > 0;
    }

    public function addSuccess(string $field, string $message): void
    {
        $this->successes[] = [
            'field' => self::cleanField($field),
            'message' => $message
        ];
    }

    public function addWarning(string $field, string $message): void
    {
        $this->warnings[] = [
            'field' => self::cleanField($field),
            'message' => $message
        ];
    }

    public function response(?string $successMessage = null, ?string $failMessage = null, ?array $args = null): ResponseForm
    {
        if ($this->isSuccess()) {
            Transaction::commit();
        } else {
            Transaction::rollback();
        }

        return new ResponseForm($this, $successMessage, $failMessage, $args);
    }

    public function buildDetails(): array
    {
        $details = [];
        $onErrors = [];
        $onWarnings = [];

        foreach ($this->errors as $error) {
            $onErrors[] = $error['field'];
            $details[] = [
                'fields' => [$error['field']],
                'success' => false,
                'message' => $error['message']
            ];
        }

        foreach ($this->warnings as $warning) {
            $onWarnings[] = $warning['field'];
            $details[] = [
                'fields' => [$warning['field']],
                'success' => false,
                'message' => $warning['message']
            ];
        }
        foreach ($this->successes as $success) {
            if (in_array($success['field'], $onErrors)) {
                continue;
            }
            if (in_array($success['field'], $onWarnings)) {
                continue;
            }
            $details[] = [
                'fields' => [$success['field']],
                'success' => true,
                'message' => $success['message']
            ];
        }
        return $details;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }
}