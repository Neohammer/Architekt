<?php

namespace tests\Architekt\DB;

use Architekt\DB\DBConnexion;
use Architekt\DB\Exceptions\MissingConfigurationException;
use PHPUnit\Framework\TestCase;

class DBConnexionTest extends TestCase
{
    public function test(): void
    {
        DBConnexion::add(
            'test',
            DBConnexion::MYSQL,
            'hostnamegiven',
            'usergiven',
            'passwordgiven'
        );

        $this->assertInstanceOf(DBConnexion::class, DBConnexion::get('test'));

        $this->assertEquals(DBConnexion::get('test'), DBConnexion::get('test'));
    }

    public function test_missingConnexionThrowExceptionWhenCalled(): void
    {
        $this->expectException(MissingConfigurationException::class);

        DBConnexion::get("UnknownDatabaseTest");
    }
}