<?php

namespace Architekt\DB\Motors\Mysql;

use Architekt\DB\Database;
use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\EntityInterface;
use Architekt\DB\Motors\DBMotorDeleteInterface;
use Architekt\DB\Motors\DBMotorInterface;

class MysqlDelete implements DBMotorDeleteInterface
{
    private EntityInterface $entity;
    private DBEngineInterface $engine;

    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
        $this->engine = Database::engine($entity->_databaseIdentifier());
    }

    public function execute(): bool
    {
        return !is_null($this->engine->execute($this->build()));
    }

    public function build(): string
    {
        $parts = [
            'DELETE',
            sprintf('FROM %s', $this->engine->quote($this->entity->_table())),
            sprintf(
                'WHERE %s',
                sprintf(
                    '%s=%d',
                    $this->engine->quote($this->entity->_primaryKey()),
                    $this->entity->_primary()
                )
            ),
            'LIMIT 1'
        ];

        return implode(' ', $parts);
    }
}