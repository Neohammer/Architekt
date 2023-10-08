<?php

namespace tests\Architekt\Auth;

use Architekt\Auth\Token;
use Architekt\DB\DBConnexion;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use PHPUnit\Framework\TestCase;
use tests\Auth\UserTestSample;


class TokenTestSample extends Token
{
    public static function install(): void
    {
        self::uninstall();
        DBConnexion::get()->datatableCreate(
            (new DBDatatable('token'))
            ->addColumn(DBDatatableColumn::buildAutoincrement())
            ->addColumn(DBDatatableColumn::buildInt('user_id',4))
            ->addColumn(DBDatatableColumn::buildDatetime('datetime'))
            ->addColumn(DBDatatableColumn::buildString('key', 32))
            ->addColumn(DBDatatableColumn::buildString('code', 32))
        );
    }

    public static function uninstall(): void
    {
        DBConnexion::get()->datatableDelete(new DBDatatable('token'));
    }

    public static function buildFake(): static
    {
        return self::build(
            UserTestSample::buildFake(),
            'test',
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