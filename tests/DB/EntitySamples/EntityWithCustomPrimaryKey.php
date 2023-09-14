<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Database;
use Architekt\DB\Entity;

class EntityWithCustomPrimaryKey extends Entity
{
    protected static ?string $_table = 'test_table_cpktp';
    protected static string $_primaryKey = 'uid';


    public static function _test_createTable(): void
    {
        $query = 'CREATE TABLE IF NOT EXISTS `test_table_cpktp` (`uid` INT UNSIGNED NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NULL DEFAULT NULL , `active` TINYINT(1) UNSIGNED NULL DEFAULT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;';
        Database::engine()->execute($query);
    }

    public static function _test_clearTable(): void
    {
        self::_test_createTable();
        $query = 'TRUNCATE TABLE `test_table_cpktp`;';
        Database::engine()->execute($query);
    }

    public static function _test_dropTable(): void
    {
        $query = 'DROP TABLE IF EXISTS test_table_cpktp';
        Database::engine()->execute($query);
    }

    public static function _test_createRow(
        ?string $name = null,
        ?string $active = null
    ): int {
        self::_test_createTable();

        $query = "INSERT INTO `test_table_cpktp` 
                        SET name= " . (null === $name ? 'null' : '"' .  Database::engine()->escape($name) . '"') . ","
            . "active= " . (null === $active ? 'null' : '"' . Database::engine()->escape($active) . '"');
        Database::engine()->execute($query);

        return Database::engine()->lastInsertId();
    }

    public static function _test_createSample(): self
    {
        return new static(
            static::_test_createRow(
                'test1',
                '1'
            )
        );
    }
}
