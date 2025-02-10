<?php

namespace Architekt\Form;

use Architekt\Response\FormResponse as ResponseForm;
use Architekt\Transaction;

class Validation
{
    private array $customFields = [];
    private array $errors;
    private array $successes;
    private array $warnings;
    private array $args;

    public static function cleanField(string $field, string $fieldFormat = '%s', array $customFields = []): string
    {
        if (array_key_exists($field, $customFields)) {
            return $customFields[$field];
        }

        $replacers = [
            '][' => '-',
            '[' => '',
            ']' => ''
        ];

        $final = str_replace(array_keys($replacers), $replacers, sprintf($fieldFormat, $field));

        if (array_key_exists($final, $customFields)) {
            return $customFields[$final];
        }
        return $final;
    }

    public function __construct()
    {
        Transaction::start();
        $this->errors = [];
        $this->successes = [];
        $this->warnings = [];
        $this->args = [];
    }

    public function addResponse(ResponseForm $formResponse, string|array $sprintfOrArray = []): static
    {
        foreach ($formResponse->validation->errors as $error) {
            $field = $error['field'];
            if (is_array($field) && in_array($field, $sprintfOrArray)) {
                $error['field'] = self::cleanField(sprintfOrArray[$field]);
            }elseif(is_string($sprintfOrArray)){
                $error['field'] = self::cleanField(sprintf($sprintfOrArray, $field));
            }
            $this->errors[] = $error;
        }
        foreach ($formResponse->validation->successes as $success) {
            $field = $success['field'];
            if (is_array($field) && in_array($field, $sprintfOrArray)) {
                $success['field'] = self::cleanField($sprintfOrArray[$field]);
            }elseif(is_string($sprintfOrArray)){
                $success['field'] = self::cleanField(sprintf($sprintfOrArray, $field));
            }
            $this->successes[] = $success;
        }

        $this->args = array_merge($this->args, $formResponse->args());

        return $this;
    }

    public function addError(string $field, string $message, string $fieldFormat = '%s'): void
    {
        $this->errors[] = [
            'field' => self::cleanField($field, $fieldFormat, $this->customFields),
            'message' => $message
        ];
    }

    public function useFields(array $fields): void
    {
        $this->customFields = $fields;
    }

    public function isSuccess(): bool
    {
        return !sizeof($this->errors) > 0;
    }

    public function addSuccess(string $field, string $message, string $fieldFormat = '%s'): void
    {
        $this->successes[] = [
            'field' => self::cleanField($field, $fieldFormat, $this->customFields),
            'message' => $message
        ];
    }

    public function addWarning(string $field, string $message, string $fieldFormat = '%s'): void
    {
        $this->warnings[] = [
            'field' => self::cleanField($field, $fieldFormat, $this->customFields),
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

        if ($args) {
            $this->args = array_merge($this->args, $args);
        }

        return new ResponseForm($this, $successMessage, $failMessage, $this->args);
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

    public function errors(): array
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[$error['field']] = $error['message'];
        }

        return $errors;
    }

    public function warnings(): array
    {
        $warnings = [];
        foreach ($this->warnings as $warning) {
            $warnings[$warning['field']] = $warning['message'];
        }

        return $warnings;
    }

    public function successes(): array
    {
        $successes = [];
        foreach ($this->successes as $success) {
            $successes[$success['field']] = $success['message'];
        }

        return $successes;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }
}