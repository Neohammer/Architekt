<?php

namespace Architekt\Auth\Access\Attributes;

class LoggedUserAttribute
{
    private ?bool $hasToBeLogged;
    public function __construct(?bool $hasToBeLogged)
    {
        $this->hasToBeLogged = $hasToBeLogged;
    }

    public function hasToBeLogged(): ?bool
    {
        return $this->hasToBeLogged;
    }

    public static function parse(array $methodAttributes): static
    {
        $logged = null;

        if(isset($methodAttributes['Logged'])){
            $logged = true;
        }
        if(isset($methodAttributes['Unlogged'])){
            $logged = false;
        }

        return new self($logged);
    }
}