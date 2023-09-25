<?php

namespace Architekt\Auth\Access\Attributes;

class DependencyAttribute
{
    private string $value;
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}