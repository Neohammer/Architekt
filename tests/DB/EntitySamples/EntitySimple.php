<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Database;
use Architekt\DB\Entity;

class EntitySimple extends Entity
{
    protected static ?string $_table = 'sql_test_entity';
    protected static ?string $_table_prefix = '';

    public static function _test_createTable(string $instanceName = 'main'): void
    {
        $query = 'CREATE TABLE IF NOT EXISTS `sql_test_entity` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NULL DEFAULT NULL , `active` TINYINT(1) UNSIGNED NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';
        Database::engine($instanceName)->execute($query);
    }

    public static function _test_clearTable(string $instanceName = 'main'): void
    {
        self::_test_createTable();
        $query = 'TRUNCATE TABLE `sql_test_entity`;';
        Database::engine($instanceName)->execute($query);
    }

    public static function _test_dropTable(string $instanceName = 'main'): void
    {
        $query = 'DROP TABLE IF EXISTS sql_test_entity';
        Database::engine($instanceName)->execute($query);
    }

    public static function _test_createRow(
        ?string $name = null,
        null|int|string $active = null,
        string $instanceName = 'main'
    ): string|int {
        self::_test_createTable($instanceName);

        $query = "INSERT INTO `sql_test_entity` 
                        SET name= " . (null === $name ? 'null' : '"' .  Database::engine()->escape($name) . '"') . ","
            . "active= " . (null === $active ? 'null' : '"' . Database::engine()->escape($active) . '"');
        Database::engine($instanceName)->execute($query);

        return Database::engine($instanceName)->lastInsertId();
    }

    public static function _test_createSample(string $instanceName = 'main'): self
    {
        return new static(
            self::_test_createRow(
                'test1',
                '1',
                $instanceName
            )
        );
    }

    public static function _test_createSample2(string $instanceName = 'main'): self
    {
        return new static(
            static::_test_createRow(
                'test2',
                '0',
                $instanceName
            )
        );
    }
}
