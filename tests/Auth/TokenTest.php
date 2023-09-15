<?php

namespace tests\Architekt\Auth;

use Architekt\Auth\Token;
use Architekt\DB\Database;
use PHPUnit\Framework\TestCase;
use tests\Auth\UserTestSample;


class TokenTestSample extends Token
{
    public static function install(): void
    {
        self::uninstall();

        $query = 'CREATE TABLE  `token`  (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `user_id` INT UNSIGNED NOT NULL , `datetime` DATETIME NOT NULL , `key` VARCHAR(32) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';
        Database::engine()->execute($query);
        $query = 'ALTER TABLE `token` ADD INDEX(`user_id`);';
        Database::engine()->execute($query);
    }

    public static function uninstall(): void
    {
        $query = 'DROP TABLE IF EXISTS `token`;';
        Database::engine()->execute($query);
    }

    public static function buildFake(): static
    {
        return self::build(
            UserTestSample::buildFake(),
            '+5 minutes'
        );
    }

    public function _hasExpired(): bool
    {
        return $this->hasExpired();
    }
}

class TokenTest extends TestCase
{
    protected function setUp(): void
    {
        TokenTestSample::install();
    }

    protected function tearDown(): void
    {
        TokenTestSample::uninstall();
    }

    public static function test_(): void
    {
        $token = TokenTestSample::buildFake();

        self::assertFalse($token->_hasExpired());
        self::assertEquals(32 , strlen($token->key()));
    }
}