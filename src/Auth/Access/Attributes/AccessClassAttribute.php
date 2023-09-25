<?php

namespace Architekt\Auth\Access\Attributes;

class AccessClassAttribute
{
    public string $code;
    public string $name;
    public string $description;

    public function __construct(
        string  $code,
        ?string $name = null,
        ?string $description = null
    )
    {
        $this->code = $code;
        $this->name = $name ?? $code;
        $this->description = $description ?? 'No description given';
    }
}