<?php

namespace Architekt\Auth\Access\Attributes;

class DescriptionAttribute
{
    public string $description;
    public function __construct(?string $description)
    {
        $this->description = $description ?? "No description given";
    }

    public static function parse(array $methodAttributes): static
    {
        return new self(
            $methodAttributes['Description'][0][0] ?? null
        );
    }
}