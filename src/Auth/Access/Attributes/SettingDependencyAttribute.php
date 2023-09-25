<?php

namespace Architekt\Auth\Access\Attributes;

class SettingDependencyAttribute
{
    public string $plugin;
    public string $code;
    public string $value;

    public function __construct(
        string $plugin,
        string $code,
        string $value,
    )
    {
        $this->plugin = $plugin;
        $this->code = $code;
        $this->value = $value;
    }
}