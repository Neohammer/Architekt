<?php

namespace Architekt\DB\Motors;

use Architekt\DB\EntityInterface;

interface DBMotorDeleteInterface
{
    public function __construct(EntityInterface $entity);

    public function build(): string;

    public function execute(): bool;

}