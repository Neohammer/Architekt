<?php

namespace Architekt\Auth\Access\Attributes;

class SettingAttribute
{
    public string $code;
    public mixed $value;

    public function __construct(
        string $code,
        mixed $value,
    )
    {
        $this->code = $code;
        $this->value = $value;
    }
}