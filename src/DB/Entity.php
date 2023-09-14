<?php

namespace Architekt\DB;


use Architekt\DB\Engines\DBEngineInterface;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Motors\DBMotorInterface;
use Architekt\DB\Motors\DBMotorSelectInterface;

class Entity implements EntityInterface
{
    protected static ?string $_database = APPLICATION_MAIN_DATABASE;
    protected static ?string $_databaseIdentifier = 'main';
    protected static ?string $_table;
    protected static ?string $_table_prefix = TABLE_PREFIX;
    protected static string $_primaryKey = 'id';
    protected static ?string $_strangerKey = null;
    protected static string $_labelField = 'name';

    private ?DBMotorSelectInterface $_search = null;

    private bool $_loaded = false;
    private array $_datas = array();
    private array $_datas_loaded = array();

    final public function __construct(?int $primary = null)
    {
        if ($primary) {
            $this->_search()->filter($this,$primary);
            $this->_next();
            $this->_search = null;
        }

        if (!$this->_isLoaded()) {
            $this->_setDefaults();
        }
    }

    public function _search(): DBMotorSelectInterface
    {
        return $this->_search ?? $this->_initSearch();
    }

    public function _initSearch(): DBMotorSelectInterface
    {
        return $this->_search = $this->_motor()->select($this);
    }

    final public function _motor(): DBMotorInterface
    {
        return $this->_engine()->motor();
    }

    final public function _engine(): DBEngineInterface
    {
        return Database::engine(self::_databaseIdentifier());
    }

    final public function _databaseIdentifier(): string
    {
        return static::$_databaseIdentifier;
    }

    final public function _next(): bool
    {
        if (null !== $this->_search) {
            if ($this->_datas = $this->_search->fetch() ?? []) {
                $this->_loaded = true;
                $this->_resetChanges();

                return true;
            }
        }

        $this->_reset();

        return false;
    }

    private function _resetChanges(): static
    {
        $this->_datas_loaded = $this->_datas;

        return $this;
    }

    private function _reset(): static
    {
        $this->_datas = [];
        $this->_loaded = false;
        $this->_resetChanges();

        return $this;
    }

    final public function _isLoaded(): bool
    {
        return true === $this->_loaded;
    }

    final public function _forceLoaded(): self
    {
        $this->_loaded = true;

        return $this;
    }

    protected function _setDefaults(): void
    {
        //no default value
    }

    public function _database(): string
    {
        return static::$_database;
    }

    final public function _table(bool $withPrefix = true): string
    {
        if (!isset(static::$_table)) {
            throw new MissingConfigurationException('Entity static $_table must be set');
        }

        return sprintf(
            '%s%s',
            $withPrefix ? static::$_table_prefix : '',
            static::$_table
        );
    }

    public function _delete(): bool
    {
        if (!$this->_isLoaded()) {
            return false;
        }

        $delete = $this->_motor()->delete($this);
        if ($delete) {
            $this->_reset();
        }

        return $delete;
    }

    public function labelOption(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return $this->_get(static::$_labelField);
    }

    public function _get(?string $key = null): mixed
    {
        $args = func_get_args();
        if (sizeof($args) == 1) {
            return $this->_datas[$key] ?? null;
        }

        return $this->_datas ?? [];
    }

    public function _resultsToArray(): array
    {
        return $this->_results(true);
    }

    public function _results(bool $toArray = false): array
    {
        $tab = array();
        while ($this->_next()) {
            $tab[$this->_primary()] = $toArray ? $this->_get() : clone $this;
        }
        return $tab;
    }

    final public function _primary(): string|int|null
    {
        return $this->_get($this->_primaryKey());
    }

    final public function _primaryKey(): string
    {
        return static::$_primaryKey;
    }

    final public function _strangerKey(): string
    {
        if(static::$_strangerKey){
            return static::$_strangerKey;
        }

        return sprintf(
                '%s_%s',
                $this->_table(false),
                $this->_primaryKey()
        );
    }

    public function isFieldValueUnique(string $field, mixed $value, array $filter = []): bool
    {
        $exists = new static();
        $exists->_search()
            ->filter($field, $value)
            ->limit(1);
        if (count($filter) > 0) {
            $exists->_search()->filter($filter);

        }

        if ($this->_isLoaded()) {
            $exists->_search()
                ->filterNot($this);
        }

        return !$exists->_resultsCount();
    }

    public function _resultsCount(): ?int
    {
        return $this->_search->resultsNb();
    }

    public final function _hasToBeSaved(): bool
    {
        return !$this->_isLoaded() || $this->_hasDiff();
    }

    public function _hasDiff(): bool
    {
        return false !== $this->_diff();
    }

    public function _diff(): bool|array
    {
        if ($this->_datas_loaded !== $this->_datas) {
            return array_diff_assoc($this->_datas, $this->_datas_loaded);
        }

        return false;
    }

    public function _save(bool $forceInsert = false): bool
    {
        $upsert = $this->_motor()->upsert($this);

        if (!$upsert->execute()) {
            return false;
        }

        if ($upsert->isInsert()) {
            $this
                ->_setPrimary($this->_engine()->lastInsertId())
                ->_loaded = true;
        }
        $this->_resetChanges();

        return true;
    }

    private function _setPrimary(int|string $primary): self
    {
        return $this->_set($this->_primaryKey(), $primary);
    }

    public function _set(mixed $arg1, mixed $arg2 = null): self
    {
        if (is_array($arg1)) {
            foreach ($arg1 as $key => $value) {
                if ($value instanceof Entity) {
                    if(is_numeric($key)) {
                        $this->_set($value);
                        continue;
                    }
                }

                $this->_set($key, $value);
            }

            return $this;
        }

        if ($arg1 instanceof Entity) {
            return $this->_set(
                $arg1->_strangerKey(),
                $arg1->_primary()
            );
        }

        $this->_datas[$arg1] = $arg2 instanceof Entity ? $arg2->_primary():$arg2;

        return $this;
    }

    public function _isEqualTo(EntityInterface $entity): bool
    {
        $compareTo = $entity->_compare();
        foreach ($this->_compare() as $k => $value) {
            if ($value !== $compareTo[$k]) {
                return false;
            }
        }
        return true;
    }

    public function _compare(): array
    {
        return [
            get_class($this),
            $this->_isLoaded(),
            $this->_primary()
        ];
    }

    public function _isSameClass(mixed $entity): bool
    {
        return get_class($this) === get_class($entity);
    }

    public function _isNull($key): bool
    {
        return $this->_has($key) && $this->_get($key) === null;
    }

    public function _has(): bool
    {
        $args = func_get_args();
        if (sizeof($args) == 1) {
            return array_key_exists($args[0], $this->_datas);
        } else {
            return sizeof($this->_datas) > 0;
        }
    }
}
