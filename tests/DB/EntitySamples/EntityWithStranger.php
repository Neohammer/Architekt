<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Database;
use Architekt\DB\Entity;

class EntityWithStranger extends Entity
{
    protected static ?string $_table = 'sql_test_entity_stranger';

    public static function _test_createTable(): void
    {
        $query = 'CREATE TABLE IF NOT EXISTS `sql_test_entity_stranger` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NULL DEFAULT NULL , `active` TINYINT(1) UNSIGNED NULL DEFAULT NULL  , `sql_test_entity_id` SMALLINT(3) UNSIGNED NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';
        Database::engine()->execute($query);
    }

    public static function _test_clearTable(): void
    {
        self::_test_createTable();
        $query = 'TRUNCATE TABLE `sql_test_entity_stranger`;';
        Database::engine()->execute($query);
    }

    public static function _test_dropTable(): void
    {
        $query = 'DROP TABLE IF EXISTS sql_test_entity_stranger';
        Database::engine()->execute($query);
    }

    public static function _test_createRow(
        ?string $name = null,
        ?string $active = null,
        ?EntitySimple $entitySimple = null,
    ): int {
        self::_test_createTable();

        $query = "INSERT INTO `sql_test_entity_stranger` 
                        SET name= " . (null === $name ? 'null' : '"' .  Database::engine()->escape($name) . '"') . ","
            . "active= " . (null === $active ? 'null' : '"' . Database::engine()->escape($active) . '"') . ","
            . "sql_test_entity_id= " . (null === $entitySimple ? 'null' : '"' . Database::engine()->escape($entitySimple->_primary()) . '"');
        Database::engine()->execute($query);

        return Database::engine()->lastInsertId();
    }

    public static function _test_createSample(): self
    {
        $entitySimple = EntitySimple::_test_createSample();
        return new self(
            self::_test_createRow(
                'teststranger1',
                '2',
                $entitySimple
            )
        );
    }
}
