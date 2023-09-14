<?php

namespace tests\Architekt\DB;

use Architekt\Application;
use Architekt\Configurator;
use Architekt\Http\Environment;
use Architekt\Http\Exceptions\InvalidServerConfigurationException;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    public function test_serverName_returnAsExpected(): void
    {
        $_SERVER['SERVER_NAME'] = 'test.server.name';
        $this->assertSame('test.server.name', Environment::serverName());
    }

    public function test_serverName_whenEmptyReturnInvalidServerConfigurationException(): void
    {
        $old = $_SERVER['SERVER_NAME'];
        unset($_SERVER['SERVER_NAME']);

        $this->expectException(InvalidServerConfigurationException::class);
        Environment::serverName();

        $_SERVER['SERVER_NAME'] = $old;
    }

    public function test_willReturnUrlAsExpected(): void
    {
        self::assertSame('//test.mon-domaine.fr/', Environment::url('testServer'));
    }

    public function test_willReturnAnErrorOnWrongServer(): void
    {
        $_SERVER['SERVER_NAME'] = 'mauvais.domaine.fr';
        Environment::add('testAutre', ['website' => 'http://monwebsite.com']);

        self::expectException(InvalidServerConfigurationException::class);
        Environment::get();
    }


}
