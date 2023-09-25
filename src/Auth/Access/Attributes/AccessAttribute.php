<?php

namespace Architekt\Auth\Access\Attributes;

class AccessAttribute
{
    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

}