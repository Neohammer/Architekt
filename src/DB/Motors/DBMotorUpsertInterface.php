<?php

namespace Architekt\DB\Motors;

use Architekt\DB\EntityInterface;

interface DBMotorUpsertInterface
{
    public function __construct(EntityInterface $entity);

    public function build(): string;

    public function execute(): bool;

    public function isInsert(): bool;

}