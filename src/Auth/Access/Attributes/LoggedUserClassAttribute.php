<?php

namespace Architekt\Auth\Access\Attributes;

class LoggedUserClassAttribute
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

        if(isset($methodAttributes['UserLogged'])){
            $logged = true;
        }
        if(isset($methodAttributes['UserUnlogged'])){
            $logged = false;
        }

        return new self($logged);
    }
}