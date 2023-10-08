<?php

namespace Architekt\Auth\Access\Attributes;

class SettingAttribute
{
    public function __construct(
        public string $code,
        public string $subCode,
        public mixed $value,
    )
    {

    }
}