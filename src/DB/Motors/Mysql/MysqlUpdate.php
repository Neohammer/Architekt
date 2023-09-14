<?php

namespace Architekt\DB\Motors\Mysql;

use Architekt\DB\Database;
use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\EntityInterface;
use Architekt\DB\Motors\DBMotorInterface;

class MysqlUpdate
{
    private EntityInterface $entity;
    private DBEngineInterface $engine;

    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
        $this->engine = Database::engine($entity->_databaseIdentifier());
    }

    public function build(): string
    {
        $changes = $this->entity->_get();

        $values = [];
        foreach ($changes as $key => $value) {
            if ($key === $this->entity->_primaryKey()) {
                continue;
            }
            $values[] = sprintf(
                '%s=%s',
                $this->engine->quote($key),
                is_null($value) ? 'null' : sprintf('"%s"', $this->engine->escape($value))
            );
        }

        return sprintf(
            'UPDATE %s SET %s WHERE %s="%s"',
            $this->engine->quote($this->entity->_table()),
            implode(', ', $values),
            $this->engine->quote($this->entity->_primaryKey()),
            $this->engine->escape($this->entity->_primary())
        );
    }
}