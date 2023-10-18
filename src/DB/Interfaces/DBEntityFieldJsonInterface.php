<?php

namespace Architekt\DB\Interfaces;

interface DBEntityFieldJsonInterface
{
    public function __construct(DBEntityInterface $entity, string $field);

    public function toString(): string;
}