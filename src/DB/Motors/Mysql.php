<?php

namespace Architekt\DB\Motors;

use Architekt\DB\EntityInterface;
use Architekt\DB\Motors\Mysql\MysqlDelete;
use Architekt\DB\Motors\Mysql\MysqlSelect;
use Architekt\DB\Motors\Mysql\MysqlUpsert;

class Mysql implements DBMotorInterface
{
    public function delete(EntityInterface $entity): bool
    {
        return (new MysqlDelete($entity))->execute();
    }

    public function select(EntityInterface $entity): DBMotorSelectInterface
    {
        return new MysqlSelect($this, $entity);
    }

    public function upsert(EntityInterface $entity): DBMotorUpsertInterface
    {
        return new MysqlUpsert($entity);
    }

}