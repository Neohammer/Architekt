<?php

namespace Architekt\DB\Abstraction;

class Query
{
    public string $command;
    public ?array $params;

    public function __construct(
        string $command,
        array  $params = []
    )
    {
        $this->command = $command;
        $this->params = $params;
    }

    public function parse(): string
    {
        $command = $this->command;
        foreach ($this->params as $key => $value) {
            $command = str_replace($key, $value, $command);
        }

        return $command;
    }
}