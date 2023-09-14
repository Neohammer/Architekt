<?php

namespace Architekt\DB\Motors\Mysql;

use Architekt\DB\Database;
use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\EntityInterface;
use Architekt\DB\Motors\DBMotorInterface;

class MysqlInsert
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

        $keys = $values = [];
        foreach ($changes as $key => $value) {
            $keys[] = $this->engine->quote($key);
            $values[] = is_null($value) ? 'null' : sprintf('"%s"', $this->engine->escape($value));
        }

        if ($keys) {
            $parts = [
                sprintf(
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $this->engine->quote($this->entity->_table()),
                    implode(', ', $keys),
                    implode(', ', $values),
                )
            ];

            return implode(' ', $parts);
        }

       /* var_dump(sprintf(
            'INSERT INTO %s VALUES()',
            $this->engine->quote($this->entity->_table())
        ));*/
        return sprintf(
            'INSERT INTO %s VALUES()',
            $this->engine->quote($this->entity->_table())
        );
    }

}