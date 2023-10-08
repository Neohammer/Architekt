<?php

namespace Architekt\Auth\Access\Attributes;

class SettingDependencyAttribute
{
    public function __construct(
        public string $controllerCode,
        public string $code,
        public string $subCode,
        public int|bool|string $value
    )
    {
    }
}