<?php

namespace Architekt\View;

use Architekt\Http\Request;

class ListFilter
{
    private string $name;

    public function __construct(
        string $name
    )
    {
        $this->name = $name;

        $filters = Request::sessionArray('filters', []);
        if (!array_key_exists($this->name, $filters)) {
            $filters[$this->name] = [];
        }

        Request::sessionSet('filters', $filters);
    }

    public function _get(string $key): mixed
    {
        return Request::sessionArray('filters')[$this->name][$key] ?? null;
    }

    public function _getAll(): mixed
    {
        return Request::sessionArray('filters')[$this->name] ?? null;
    }

    public function _set(string $key, mixed $value): self
    {
        $filters = Request::sessionArray('filters');
        $filters[$this->name][$key] = $value;
        Request::sessionSet('filters', $filters);

        return $this;
    }

}