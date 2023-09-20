<?php

namespace Architekt\Form;

use Architekt\Response\FormResponse as ResponseForm;
use Architekt\Transaction;

class Validation
{
    private array $errors;
    private array $successes;

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

    public function response(?string $successMessage = null, ?string $failMessage = null, ?array $args = null): ResponseForm
    {
        if ($this->isSuccess()) {
            Transaction::commit();
        } else {
            Transaction::rollback();
        }

        return new ResponseForm($this, $successMessage, $failMessage, $args);
    }

    public function buildErrorsDetails(): array
    {
        $details = [];
        $onErrors = [];
        foreach ($this->errors as $error) {
            $onErrors[] = $error['field'];
            $details[] = [
                'fields' => [$error['field']],
                'success' => false,
                'message' => $error['message']
            ];
        }
        foreach ($this->successes as $success) {
            if (in_array($success['field'], $onErrors)) {
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
}