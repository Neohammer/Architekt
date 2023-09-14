<?php

namespace Architekt\DB\Motors\Mysql;

use Architekt\DB\Database;
use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\EntityInterface;
use Architekt\DB\Motors\DBMotorInterface;
use Architekt\DB\Motors\DBMotorUpsertInterface;

class MysqlUpsert implements DBMotorUpsertInterface
{
    private EntityInterface $entity;
    private DBEngineInterface $engine;
    private ?bool $added;

    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
        $this->added = null;
        $this->engine = Database::engine($this->entity->_databaseIdentifier());
    }

    public function execute(): bool
    {
        return !is_null($this->engine->execute($this->build()));
    }

    public function build(): string
    {
        if ($this->entity->_isLoaded()) {
            $this->added = false;
            return $this->update()->build();
        }
        $this->added = true;

        return $this->insert()->build();
    }

    private function update(): MysqlUpdate
    {
        return (new MysqlUpdate(
            $this->entity
        ));
    }

    private function insert(): MysqlInsert
    {
        return (new MysqlInsert(
            $this->entity
        ));
    }

    public function isInsert(): bool
    {
        return true === $this->added;
    }
}