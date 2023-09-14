<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Database;
use Architekt\DB\Entity;

class EntityWithOtherTable extends Entity
{
    protected static ?string $_table = 'table_other_entity';
    protected static ?string $_table_prefix = '';

    public static function _test_createTable(): void
    {
        $query = 'CREATE TABLE IF NOT EXISTS `table_other_entity` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NULL DEFAULT NULL , `active` TINYINT(1) UNSIGNED NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';
        Database::engine()->execute($query);
    }

    public static function _test_clearTable(): void
    {
        self::_test_createTable();
        $query = 'TRUNCATE TABLE `table_other_entity`;';
        Database::engine()->execute($query);
    }

    public static function _test_dropTable(): void
    {
        $query = 'DROP TABLE IF EXISTS table_other_entity';
        Database::engine()->execute($query);
    }

    public static function _test_createRow(
        ?string $name = null,
        ?string $active = null
    ): int {
        self::_test_createTable();

        $query = "INSERT INTO `table_other_entity` 
                        SET name= " . (null === $name ? 'null' : '"' . Database::engine()->escape($name) . '"') . ","
            . "active= " . (null === $active ? 'null' : '"' . Database::engine()->escape($active) . '"');
        Database::engine()->execute($query);

        return Database::engine()->lastInsertId();
    }

    public static function _test_createSample(): self
    {
        return new self(
            self::_test_createRow(
                'test1',
                '1'
            )
        );
    }

    public function _test_setDatabase(string $database): self
    {
        $this->_database = $database;
        return $this;
    }

    public function _test_setTable(string $table): self
    {
        $this->_table = $table;
        return $this;
    }

    public function _test_setTablePrefix(string $prefix): self
    {
        $this->_table_prefix = $prefix;
        return $this;
    }

    public function _test_setLabel(string $label): self
    {
        $this->_labelField = $label;
        return $this;
    }
}
