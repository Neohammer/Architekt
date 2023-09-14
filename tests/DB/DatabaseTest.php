<?php

namespace tests\Architekt\DB;

use Architekt\DB\Database;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Engines\DBEngineInterface;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function testGetInstanceWithSameNameReturnSameReferenceObject(): void
    {
        Database::configure(
            'test',
            Database::MYSQLI,
            'host',
            'user',
            'mdp',
            'testdatabase'
        );

        $instance1 = Database::engine();
        $instance2 = Database::engine('test');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetInstanceWithDifferentNameReturnDifferentObjectReference(): void
    {
        $instance1 = Database::engine();
        $instance2 = Database::engine();

        $this->assertSame($instance1, $instance2);
    }

    public function testGetInstanceWithUnknownNameReturnException(): void
    {
        $this->expectException(MissingConfigurationException::class);
        Database::engine('testUnknownName');
    }

    public function testCloneReturnNewConnexion(): void
    {
        $this->assertNotSame(
            Database::engine(),
            Database::clone('main', Database::PDO, 'pdo')
        );
    }
}
