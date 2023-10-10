<?php

namespace Architekt\DB\Translators;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBConnexion;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBEntity;
use Architekt\DB\DBRecordColumn;
use Architekt\DB\DBRecordRow;
use Architekt\DB\DBRecordSearchFetcher;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Interfaces\DBEntityInterface;
use Architekt\DB\Interfaces\DBRecordSearchInterface;

class DBEntityRecordSearchTranslator
{
    private DBConnexion $connexion;
    private DBRecordSearchInterface $search;
    private DBEntityInterface $entity;

    public function __construct(DBEntityInterface $entity)
    {
        $this->entity = $entity;
        $this->connexion = DBConnexion::get($entity->_databaseIdentifier());
        $this->search = $this->connexion->recordSearch();

        $this->datatable($entity);
    }

    public function debugQuery(): Query
    {
        return $this->search->query();
    }

    public function datatable(DBEntityInterface $entity): static
    {
        $this->search->datatable(new DBDatatable($entity->_table()));

        return $this;
    }

    public function leftDatatable(DBEntityInterface $entity1, DBEntityInterface $entity2, mixed $filters = null): static
    {
        $recordRows[] = (new DBRecordRow($entity1->_table()))->and(
            $entity1->_primaryKey(), new DBRecordColumn($entity2->_table(), $entity1->_strangerKey()));

        if ($filters) {
            if (!is_array(current($filters))) {
                $filters = [$filters];
            }
            foreach ($filters as $filter) {
                $method = $filter[0];
                $target = $filter[1];
                unset($filter[0], $filter[1]);

                $extractedFilter = $this->extractFilter($target, array_values($filter));

                $table = $extractedFilter[0];
                unset($extractedFilter[0]);
                $extractedFilter = array_values($extractedFilter);

                $recordRows[] = (new DBRecordRow($table))->$method(...$extractedFilter);
            }
        }

        $this->search->datatable(
            new DBDatatable($entity2->_table()),
            $recordRows
        );

        return $this;
    }

    private function extractFilter(DBEntityInterface $entity, array $args): array
    {
        if (sizeof($args) === 0) {
            if ($entity->_isLoaded()) {
                if ($this->entity->_isSameClass($entity)) {
                    return [$entity->_table(), $entity->_primaryKey(), $entity->_primary()];
                }

                return [$entity->_table(), $entity->_strangerKey(), $entity->_primary()];
            }

            throw new MissingConfigurationException('RecordSearch with 1 param not supported (only for loaded entity)');
        }

        if (sizeof($args) === 1) {
            if (is_array($args[0])) {
                $return = [];
                foreach ($args[0] as $key => $value) {
                    $return[] = [$entity->_table(), $key, $value];
                }

                return $return;
            } else {
                if ($args[0] instanceof DBEntity) {
                    return [$entity->_table(), $args[0]->_strangerKey(), $args[0]->_primary()];
                }

                return [$entity->_table(), $entity->_primaryKey(), $args[0]];
            }
        }

        if (sizeof($args) === 2) {

            if ($args[0] instanceof DBEntityInterface) {

                if ($args[1] instanceof DBEntityInterface) {
                    return [$args[0]->_table(), $args[0]->_primaryKey(), $args[1]->_strangerKey()];
                }

                throw new MissingConfigurationException('RecordSearch with 2nd param to entity not supported when only 3 params given');
            }

            return [$entity->_table(), $args[0], $args[1]];
        }

        var_dump(func_get_args());
        throw new MissingConfigurationException(sprintf('RecordSearch with %s argument(s) not supported', sizeof($args)));
    }

    public function filter(DBEntityInterface $entity, string $method, array $args): static
    {
        $filters = $this->extractFilter($entity, $args);
        if (is_array($filters[0])) {
            foreach ($filters as $subFilter) {
                $table = $subFilter[0];
                unset($subFilter[0]);
                $this->search->filter(
                    (new DBRecordRow($table))
                        ->$method(...array_values($subFilter))
                );
            }
        }
        $table = $filters[0];
        unset($filters[0]);
        $this->search->filter(
            (new DBRecordRow($table))
                ->$method(...array_values($filters))
        );

        return $this;
    }

    public function select(DBEntity $entity, ?string $field = '*'): static
    {
        $this->search->select(new DBRecordColumn($entity->_table(), $field));

        return $this;
    }

    public function limit(int $nbRecords = 1, int $page = 1): static
    {
        $this->search->limit($nbRecords, $page);

        return $this;
    }

    public function orderAsc(DBEntity $entity, ?string $columnName = null): static
    {
        $this->search->orderAsc(new DBRecordColumn($entity->_table(), $columnName ?? $entity->_primaryKey()));

        return $this;
    }

    public function orderDesc(DBEntity $entity, ?string $columnName = null): static
    {
        $this->search->orderDesc(new DBRecordColumn($entity->_table(), $columnName ?? $entity->_primaryKey()));

        return $this;
    }

    public function recordSearchNext(DBRecordSearchFetcher $recordSearchFetcher): bool|DBEntityInterface
    {
        if (!$recordRow = $this->connexion->recordSearchNext($recordSearchFetcher)) {
            return false;
        }

        return $this->entity->_set($recordRow->aget());
    }

    public function recordSearchCount(DBRecordSearchFetcher $DBRecordSearchFetcher): ?int
    {
        return $this->connexion->recordSearchCount($DBRecordSearchFetcher);
    }

    public function recordSearchFetcher(): bool|DBRecordSearchFetcher
    {
        return $this->connexion->recordSearchFetcher($this->search);
    }

    public function and(DBEntityInterface $entity, ...$args): static
    {
        return $this->filter($entity, 'and', $args);
    }

    public function andGreater(DBEntityInterface $entity, ...$args): static
    {
        return $this->filter($entity, 'andGreater', $args);
    }

    public function andGreaterOrEqual(DBEntityInterface $entity, ...$args): static
    {
        return $this->filter($entity, 'andGreaterOrEqual', $args);
    }

    public function andLower(DBEntityInterface $entity, ...$args): static
    {
        return $this->filter($entity, 'andLower', $args);
    }

    public function andLowerOrEqual(DBEntityInterface $entity, ...$args): static
    {
        return $this->filter($entity, 'andLowerOrEqual', $args);
    }

    public function andNot(DBEntityInterface $entity, ...$args): static
    {
        return $this->filter($entity, 'andNot', $args);
    }
}