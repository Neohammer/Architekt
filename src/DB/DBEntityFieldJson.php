<?php

namespace Architekt\DB;


use Architekt\DB\Interfaces\DBEntityFieldJsonInterface;
use Architekt\DB\Interfaces\DBEntityInterface;


class DBEntityFieldJson implements DBEntityFieldJsonInterface
{
    public array $datas;

    public function __construct(private DBEntityInterface $entity, private string $field)
    {
        $this->datas = [];
    }

    public function set(string $key, mixed $value): static
    {
        $this->decode();

        $this->datas[$key] = $value;

        $this->encode();

        return $this;
    }

    public function get(string $key): mixed
    {
        $this->decode();

        if (!array_key_exists($key, $this->datas)) {
            echo 'not found';
            return null;
        }

        return $this->datas[$key];
    }

    public function decode(): void
    {
        $this->datas = json_decode($this->entity->_get($this->field , true), true);
    }

    public function encode(): void
    {
        $this->entity->_set($this->field, $this->toString());
    }

    public function toString(): string
    {
        return json_encode($this->datas);
    }
}
