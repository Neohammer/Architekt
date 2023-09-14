<?php

namespace Architekt\DB\Motors;

use Architekt\DB\EntityInterface;

interface DBMotorInterface
{
    public function select(EntityInterface $entity): DBMotorSelectInterface;

    public function delete(EntityInterface $entity): bool;

    public function upsert(EntityInterface $entity): DBMotorUpsertInterface;
}