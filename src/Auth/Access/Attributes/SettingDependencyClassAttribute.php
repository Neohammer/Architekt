<?php

namespace Architekt\Auth\Access\Attributes;

class SettingDependencyClassAttribute
{
    public function __construct(
        public string $plugin,
        public string $code
    )
    {

    }
}