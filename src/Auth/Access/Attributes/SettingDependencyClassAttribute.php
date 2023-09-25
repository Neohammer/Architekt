<?php

namespace Architekt\Auth\Access\Attributes;

class SettingDependencyClassAttribute
{
    public string $plugin;
    public string $code;

    public function __construct(
        string $plugin,
        string $code
    )
    {
        $this->plugin = $plugin;
        $this->code = $code;
    }
}