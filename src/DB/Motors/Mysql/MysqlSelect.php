<?php

namespace Architekt\DB\Motors\Mysql;

use Architekt\DB\Database;
use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\EntityInterface;
use Architekt\DB\Exceptions\InvalidParameterException;
use Architekt\DB\Motors\DBMotorInterface;
use Architekt\DB\Motors\DBMotorSelectInterface;
use Architekt\Library\Logger;

class MysqlSelect implements DBMotorSelectInterface
{
    private EntityInterface $entity;
    private array $select;
    private array $where;
    private ?array $limit;
    private array $order;
    /** @var ?EntityInterface[] */
    private ?array $join;
    /** @var ?EntityInterface[] */
    private ?array $leftJoin;
    private DBMotorInterface $motor;
    private DBEngineInterface $engine;
    private string $resourceIdentifier;

    public function __construct(DBMotorInterface $DBMotor, EntityInterface $entity)
    {
        $this->motor = $DBMotor;
        $this->entity = $entity;
        $this->select = [];
        $this->where = [];
        $this->limit = null;
        $this->order = [];
        $this->join = [];
        $this->engine = Database::engine($entity->_databaseIdentifier());
    }

    public function join(EntityInterface $entity): static
    {
        $this->join[] = sprintf(
            'INNER JOIN %s ON %s',
            $this->engine->quote($entity->_table()),
            $this->_onFilter(func_get_args(), '=')
        );

        return $this;
    }

    public function leftJoin(EntityInterface $entity): static
    {
        $this->join[] = sprintf(
            'LEFT JOIN %s ON %s',
            $this->engine->quote($entity->_table()),
            $this->_onFilter(func_get_args(), '=')
        );

        return $this;
    }

    public function filterOn(): static
    {
        $this->join[sizeof($this->join) - 1] .= " " . $this->_filter(func_get_args(), '=');

        return $this;
    }

    public function limit(int $limit, int $offset = 0): static
    {
        $this->limit = [$limit, $offset];

        return $this;
    }

    public function select(
        string|EntityInterface $fieldOrEntity,
        ?string                $aliasOrFieldWhenEntityFirst = null,
        ?string                $aliasWhenEntityWithField = null
    ): static
    {
        if ($fieldOrEntity instanceof EntityInterface) {
            $table = $fieldOrEntity->_table();
            $field = $aliasOrFieldWhenEntityFirst ? $this->engine->quote($aliasOrFieldWhenEntityFirst) : '*';
            $alias = $aliasWhenEntityWithField;
        } else {
            $table = $this->entity->_table();
            $field = $this->engine->quote($fieldOrEntity);
            $alias = $aliasOrFieldWhenEntityFirst;
        }

        $this->select[] = sprintf(
            '%s.%s%s',
            $this->engine->quote($table),
            $field,
            $alias ? sprintf(' AS "%s"', $this->engine->escape($alias)) : ""
        );

        return $this;
    }

    private function _addWhere(string|array $clause): void
    {
        if (is_array($clause)) {
            foreach ($clause as $c) {
                $this->_addWhere($c);
            }
        } else {
            $this->where[] = $clause;
        }
    }

    public function filter(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '='
            )
        );

        return $this;
    }

    public function filterOr(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '=',
                'OR'
            )
        );

        return $this;
    }

    public function filterNot(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '!='
            )
        );

        return $this;
    }

    public function filterGreater(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '>'
            )
        );

        return $this;
    }

    public function filterGreaterOrEqual(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '>='
            )
        );

        return $this;
    }

    public function filterLess(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '<'
            )
        );

        return $this;
    }

    public function filterLessOrEqual(): static
    {
        $this->_addWhere(
            $this->_filter(
                func_get_args(),
                '<='
            )
        );

        return $this;
    }

    private function _filter(array $args, string $sign, string $operator = 'AND'): string|array
    {
        if (!count($args)) {
            throw new InvalidParameterException('Filter require at least one parameter to work.');
        }

        if (is_array($args[0])) {
            $return = [];
            foreach ($args[0] as $key => $value) {
                if ($value instanceof EntityInterface && is_numeric($key)) {
                    $key = $value->_primaryKey();
                }
                $return[] = $this->_filter([$key, $value], $sign);
            }

            return $return;
        }

        $table = $this->entity->_table();
        $field = $args[0];
        $value = $args[1] ?? null;
        $tableValue = null;

        if ($args[0] instanceof EntityInterface) {
            $table = $args[0]->_table();
            $field = $args[0]->_primaryKey();

            if (sizeof($args) === 1) {
                $table = $this->entity->_table();
                $field = $args[0]->_isSameClass($this->entity) ? $args[0]->_primaryKey() : $args[0]->_strangerKey();
                $value = $args[0];
                if ($value instanceof EntityInterface && !$value->_isLoaded()) {
                    throw new InvalidParameterException("Filter first EntityInterface must be loaded to be used as filter");
                }

            } elseif (sizeof($args) === 2) {
                $value = $args[1];

            } elseif (sizeof($args) === 3) {
                $field = $args[1];
                $value = $args[2];
                if ($args[1] instanceof EntityInterface) {
                    $table = $args[0]->_table();
                    $field = $args[0]->_primaryKey();
                    $tableValue = $args[1]->_table();
                }
            } elseif (sizeof($args) === 4) {
                if (!$args[2] instanceof EntityInterface) {
                    throw new InvalidParameterException("Filter third parameter must be EntityInterface when 4 args passed");
                }
                $field = $args[1];
                $value = $args[3];
                $tableValue = $args[2]->_table();
            }
        } else {
            if (sizeof($args) === 1) {
                throw new InvalidParameterException("Filter require at least 2 arguments when first is string");
            }
        }

        if ($value instanceof EntityInterface) {
            if (!$value->_isLoaded()) {
                $tableValue = $value->_table();
                $value = $value->_primaryKey();
            } else {
                $value = $value->_primary();
            }
        }

        $openBracket = $this->openBracket;
        if ($openBracket) {
            $this->openBracket = 0;
            $this->openedBracket += $openBracket;
        }
        $endCloseBracket = 0;
        $startCloseBracket = 0;

        if ($this->closeBracket) {
            if ($this->openedBracket) {
                $startCloseBracket = $this->closeBracket;
            } else {
                $endCloseBracket = $this->closeBracket;
            }
            $this->openedBracket -= $this->closeBracket;
            $this->closeBracket = 0;
        }

        if ($this->openedBracket < 0) $this->openedBracket = 0;


        return sprintf(
            '%s%s %s%s%s ',
            $startCloseBracket ? str_repeat(')', $startCloseBracket) . ' ' : '',
            $operator,
            str_repeat('(', $openBracket),
            $this->_clause(
                $table,
                $field,
                $value,
                $sign,
                $tableValue
            ),
            str_repeat(')', $endCloseBracket),

        );
    }

    private function _onFilter(array $args, string $sign): string
    {
        if (!count($args)) {
            throw new InvalidParameterException('Filter require at least one parameter to work.');
        }

        if (sizeof($args) === 1) {
            if (!$args[0] instanceof EntityInterface) {
                throw new InvalidParameterException('Filter with one parameter require EntityInterface to work.');
            }
            $table1 = $this->entity->_table();
            $field1 = $this->entity->_primaryKey();
            $table2 = $args[0]->_table();
            $field2 = $this->entity->_strangerKey();
        }

        if (sizeof($args) === 2) {
            if (!$args[0] instanceof EntityInterface) {
                throw new InvalidParameterException('Filter with two parameters require EntityInterface on first parameter to work.');
            }
            $table1 = $this->entity->_table();
            $field1 = $this->entity->_primaryKey();
            $table2 = $args[0]->_table();
            $field2 = $args[1];
        }

        if (sizeof($args) === 3) {
            if (!$args[0] instanceof EntityInterface) {
                throw new InvalidParameterException('Filter with three parameters require EntityInterface on first parameter to work.');
            }

            $table1 = $args[0]->_table();
            if ($args[1] instanceof EntityInterface) {
                $field1 = $args[0]->_primaryKey();
                $table2 = $args[1]->_table();
                $field2 = $args[1]->_primaryKey();
            } else {
                if (!$args[2] instanceof EntityInterface) {
                    throw new InvalidParameterException('Filter with three parameters require EntityInterface on third parameter to work.');
                }
                $field1 = $args[1];
                $table2 = $args[2]->_table();
                $field2 = $args[2]->_primaryKey();
            }
        }

        if (sizeof($args) === 4) {
            if (!$args[0] instanceof EntityInterface) {
                throw new InvalidParameterException('Filter with three parameters require EntityInterface on first parameter to work.');
            }
            if (!$args[2] instanceof EntityInterface) {
                throw new InvalidParameterException('Filter with three parameters require EntityInterface on third parameter to work.');
            }

            $table1 = $args[0]->_table();
            $field1 = $args[1];
            $table2 = $args[2]->_table();
            $field2 = $args[3];
        }

        return $this->_clause(
            $table1,
            $field1,
            $field2,
            $sign,
            $table2
        );
    }

    public function fetch(): ?array
    {
        $this->resourceIdentifier = $this->execute();

        return $this->engine->fetch($this->resourceIdentifier);
    }

    private function execute(): string
    {
        if (!isset($this->resourceIdentifier)) {
            $this->resourceIdentifier = $this->engine->execute($this->build());
            if (!$this->engine->queryIsSuccess($this->resourceIdentifier)) {
                Logger::addMysqlError(
                    $this->engine->queryError($this->resourceIdentifier),
                    $this->engine->query($this->resourceIdentifier)
                );
            }
        }

        return $this->resourceIdentifier;
    }

    public function build(): string
    {
        $parts = [
            'SELECT',
            $this->buildSelect(),
            sprintf('FROM %s', $this->buildFrom())
        ];

        if ($this->where) {
            $parts[] = sprintf(
                'WHERE 1 %s%s',
                trim(implode('', $this->where)),
                str_repeat(')', $this->openedBracket)
            );
        }

        if ($this->order) {
            $parts[] = sprintf(
                'ORDER BY %s',
                implode(', ', $this->order)
            );
        }

        if ($this->limit) {
            $parts[] = sprintf(
                'LIMIT %d,%d',
                $this->limit[1],
                $this->limit[0],
            );
        }
//var_dump(implode(' ', $parts));
        return implode(' ', $parts);
    }

    private function buildSelect(): string
    {
        $automate = false;
        if (!$this->select) {
            $this->select($this->entity);
            $automate = true;
        }
        $build = join(', ', $this->select);

        if ($automate) {
            $this->select = [];
        }

        return $build;
    }

    private function buildFrom(): string
    {
        $from = $this->engine->quote($this->entity->_table());
        if ($this->entity->_database() !== $this->engine->database()) {
            $from = sprintf(
                '%s.%s',
                $this->engine->quote($this->entity->_database()),
                $this->engine->quote($this->entity->_table()),
            );
        }

        if ($this->join) {
            $from .= ' ' . implode(' ', $this->join);
        }

        return $from;
    }

    public function resultsNb(): ?int
    {
        $this->execute();
        return $this->engine->resultsNb($this->resourceIdentifier);
    }

    public function orderAsc(string|EntityInterface $fieldOrEntity, ?string $fieldWhenEntity = null): static
    {
        if ($fieldOrEntity instanceof EntityInterface) {

            return $this->_order(
                $fieldOrEntity->_table(),
                $fieldWhenEntity ?: $fieldOrEntity->_primaryKey(),
                'ASC'
            );
        }

        return $this->_order(
            $this->entity->_table(),
            $fieldOrEntity,
            'ASC'
        );
    }

    public function orderDesc(string|EntityInterface $fieldOrEntity, ?string $fieldWhenEntity = null): static
    {
        if ($fieldOrEntity instanceof EntityInterface) {

            return $this->_order(
                $fieldOrEntity->_table(),
                $fieldWhenEntity ?: $fieldOrEntity->_primaryKey(),
                'DESC'
            );
        }

        return $this->_order(
            $this->entity->_table(),
            $fieldOrEntity,
            'DESC'
        );
    }

    private function _order(string $table, string $field, string $direction): self
    {
        $this->order[] = sprintf(
            '%s.%s %s',
            $this->engine->quote($table),
            $this->engine->quote($field),
            $direction
        );

        return $this;
    }

    private function _buildValues(array $values): string
    {
        foreach ($values as $k => $v) {
            if ($v instanceof EntityInterface) {
                $v = $v->_primary();
            }
            $values[$k] = sprintf('"%s"', $this->engine->escape($v));
        }

        return sprintf('(%s)', implode(',', $values));
    }

    private function _clause(string $table, string $field, null|int|string|array $value, string $sign, ?string $tableValue = null): string
    {
        if ($value === null && !in_array($sign, ['=', '<>', '!='])) {
            throw new InvalidParameterException('Clause value can be null only for sign =, !=, <>');
        }

        if (is_array($value) && count($value) === 1) {
            $value = current($value);
        }

        if ($tableValue) {
            $value = sprintf(
                '%s%s.%s',
                $sign,
                $this->engine->quote($tableValue),
                $this->engine->quote($value)
            );
        } elseif (is_array($value)) {
            $value = ($sign !== "=" ? ' NOT IN ' : ' IN ') . $this->_buildValues($value);
        } elseif ($value === null) {
            $value = ($sign !== "=" ? ' IS NOT NULL' : ' IS NULL');
        } else {
            $value = sprintf(
                '%s"%s"',
                $sign,
                $this->engine->escape($value)
            );
        }

        return sprintf(
            '%s.%s%s',
            $this->engine->quote($table),
            $this->engine->quote($field),
            $value
        );
    }

    public function between(
        string|EntityInterface $fieldOrEntity,
        int|string             $fromOrFieldWhenEntity,
        int|string             $toOrFromWhenEntity,
        null|int|string        $toWhenEntityWithField = null
    ): static
    {

        $table = $this->entity->_table();
        $field = $fieldOrEntity;
        $from = $fromOrFieldWhenEntity;
        $to = $toOrFromWhenEntity;

        if ($fieldOrEntity instanceof EntityInterface) {
            $table = $fieldOrEntity->_table();
            $field = $fromOrFieldWhenEntity;
            $from = $toOrFromWhenEntity;
            $to = $toWhenEntityWithField;
        }

        $this->where[] = sprintf(
            ' AND %s.%s BETWEEN "%s" AND "%s"',
            $this->engine->quote($table),
            $this->engine->quote($field),
            $this->engine->escape($from),
            $this->engine->escape($to)
        );

        return $this;
    }

    private int $openBracket = 0;
    private int $openedBracket = 0;

    public function ob(): static
    {
        $this->openBracket++;

        return $this;
    }

    private int $closeBracket = 0;

    public function cb(): static
    {
        $this->closeBracket++;

        return $this;
    }
}