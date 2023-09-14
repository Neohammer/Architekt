<?php

namespace Architekt;

class Configurator
{
    /**
     * @var array
     */
    var $config;

    public function __construct()
    {
        $this->config = [];
    }

    public function setArray(array $configArray): self
    {
        $this->config = array_merge($this->config, $configArray);
        return $this;
    }

    public function get(string $key): ?string
    {
        return $this->config[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }
}