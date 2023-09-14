<?php

namespace tests\Auth;

use Architekt\Auth\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    static public function test_encryptPassword(): void
    {
        self::assertEquals('f71dbe52628a3f83a77ab494817525c6' , User::encryptPassword('toto'));
    }

    static public function test_generateHash(): void
    {
        self::assertNotEquals(User::generateHash() , User::generateHash());
    }
}
