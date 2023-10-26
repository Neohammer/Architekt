<?php

namespace Architekt\DB;


use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Interfaces\DBEntityInterface;
use Architekt\DB\Translators\DBEntityRecordSearchTranslator;

if (!defined('APPLICATION_MAIN_DATABASE')) {
    define('APPLICATION_MAIN_DATABASE', 'architekt');
}
if (!defined('TABLE_PREFIX')) {
    define('TABLE_PREFIX', '');
}

class DBEntity implements DBEntityInterface
{
    protected static ?string $_database = APPLICATION_MAIN_DATABASE;
    protected static ?string $_databaseIdentifier = 'main';
    protected static ?string $_table;
    protected static ?string $_table_prefix = TABLE_PREFIX;
    protected static string $_primaryKey = 'id';
    protected static ?string $_strangerKey = null;
    protected static string $_labelField = 'name';

    private ?DBEntityRecordSearchTranslator $_search;
    private ?DBRecordSearchFetcher $_searchFetcher;
    private ?int $_searchCount;

    private bool $_loaded = false;
    private array $_datas = array();
    private array $_datas_loaded = array();

    public function __construct(?int $primary = null)
    {
        $this->_search = null;
        $this->_searchFetcher = null;
        $this->_searchCount = null;

        if ($primary) {
            $this->_search()->and($this, $primary)->limit();
            $this->_next();
            $this->_search = null;
            $this->_searchFetcher = null;
            $this->_searchCount = null;
        }

        if (!$this->_isLoaded()) {
            $this->_setDefaults();
        }

    }

    public function _search(): DBEntityRecordSearchTranslator
    {
        return $this->_search ?? $this->_initSearch();
    }

    public function _initSearch(): DBEntityRecordSearchTranslator
    {
        $this->_searchFetcher = null;
        $this->_searchCount = null;

        return $this->_search = new DBEntityRecordSearchTranslator($this);
    }

    final public function _connexion(): DBConnexion
    {
        return DBConnexion::get($this->_databaseIdentifier());
    }

    final public function _databaseIdentifier(): string
    {
        return static::$_databaseIdentifier;
    }

    final public function _next(): bool
    {
        if (!$this->_search) {
            $this->_searchFetcher = null;
            $this->_reset();

            return false;
        }

        if (!$this->_searchFetcher) {
            $this->_searchFetcher = $this->_search->recordSearchFetcher();
            $this->_searchCount = $this->_search->recordSearchCount($this->_searchFetcher);
        }

        if (!$this->_searchFetcher) {
            $this->_search = null;
            return $this->_next();
        }

        if (!$this->_search->recordSearchNext($this->_searchFetcher)) {
            $this->_search = null;
            return $this->_next();
        }

        $this->_loaded = true;
        $this->_resetChanges();

        return true;
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

        $success = $this->_connexion()->recordDelete(
            (new DBRecordRow($this->_table()))
                ->and($this->_primaryKey(), $this->_primary())
        );

        if ($success) {
            $this->_reset();
        }

        return $success;
    }

    public function labelOption(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return $this->_get(static::$_labelField);
    }

    public function _get(?string $key = null, ?bool $originalDatas = false): mixed
    {
        $args = func_get_args();

        if (sizeof($args) == 1) {
            return $this->_datas[$key] ?? null;
        }

        return $this->_datas ?? [];
    }

    public function _resultsToArray(): array
    {
        return $this->_results(toArray: true);
    }

    public function _results(bool $toArray = false, ?string $useField = null): array
    {
        $tab = array();
        while ($this->_next()) {
            $tab[$useField ? $this->_get($useField) : $this->_primary()] = $toArray ? $this->_get() : clone $this;
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
        if (static::$_strangerKey) {
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
            ->and($this, $field, $value)
            ->limit(1);
        if (count($filter) > 0) {
            $exists->_search()->and($this, $filter);

        }

        if ($this->_isLoaded()) {
            $exists->_search()->andNot($this);
        }

        return !$exists->_resultsCount();
    }

    public function _resultsCount(): ?int
    {
        if ($this->_searchCount !== null) {
            return $this->_searchCount;
        }

        if (!$this->_search) {
            return 0;
        }

        if (!$this->_searchFetcher) {
            $this->_searchFetcher = $this->_search->recordSearchFetcher();
        }

        if (!$this->_searchFetcher) {
            return 0;
        }

        return $this->_searchCount = $this->_connexion()->recordSearchCount($this->_searchFetcher);
    }

    public final function _hasToBeSaved(): bool
    {
        return !$this->_isLoaded() || $this->_hasDiff();
    }

    public function _hasDiff(): bool
    {
        return (bool)$this->_diff();
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
        $insert = $forceInsert || !$this->_isLoaded();

        if (!$insert && !$this->_hasToBeSaved()) {
            return true;
        }

        $record = new DBRecordRow($this->_table());

        if ($diff = $this->_diff()) {
            foreach ($diff as $k => $v) {
                if ($k === $this->_primaryKey()) {
                    continue;
                }
                $record->set($k, $v);
            }
        }

        if ($insert) {
            if ($this->_loaded = $success = $this->_connexion()->recordInsert($record)) {
                $this->_setPrimary($this->_connexion()->recordInsertLast());
                $this->_loaded = true;
            }
        } else {
            $record->and($this->_primaryKey(), $this->_primary());

            $success = $this->_connexion()->recordUpdate($record);
        }

        if ($success) {
            $this->_resetChanges();
        }

        return $success;
    }

    private function _setPrimary(int|string $primary): self
    {
        return $this->_set($this->_primaryKey(), $primary);
    }

    public function _set(mixed $arg1, mixed $arg2 = null): self
    {
        if (is_array($arg1)) {
            foreach ($arg1 as $key => $value) {
                if ($value instanceof self) {
                    if (is_numeric($key)) {
                        $this->_set($value);
                        continue;
                    }
                }

                $this->_set($key, $value);
            }

            return $this;
        }

        if ($arg1 instanceof self) {
            return $this->_set(
                $arg1->_strangerKey(),
                $arg1->_primary()
            );
        }

        $this->_datas[$arg1] = $arg2 instanceof self ? $arg2->_primary() : $arg2;

        return $this;
    }

    public function _isEqualTo(DBEntityInterface $entity): bool
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
