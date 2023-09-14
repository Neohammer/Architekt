<?php

namespace tests\Architekt\DB\Motors;

use Architekt\DB\Database;
use Architekt\DB\Engines\DBEngineInterface;
use Iterator;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\EntitySimple;

class EngineTest extends TestCase
{
    protected function tearDown(): void
    {
        EntitySimple::_test_dropTable();
    }

    public static function provideEnginesForSelection(): Iterator
    {
        yield 'Mysqli' => [
            'mysqli',
            Database::clone('main', Database::MYSQLI, 'mysqli'),
            [
                'id' => '1',
                'name' => 'test1',
                'active' => '1',
            ],
            [
                'id' => '2',
                'name' => 'test2',
                'active' => '0',
            ],
        ];
        yield 'PDO' => [
            'pdo',
            Database::clone('main', Database::PDO, 'pdo'),
            [
                'id' => 1,
                'name' => 'test1',
                'active' => 1,
            ],
            [
                'id' => 2,
                'name' => 'test2',
                'active' => 0,
            ],

        ];
    }

    public static function provideEnginesForInsert(): Iterator
    {
        yield 'Mysqli' => [
            'mysqli',
            Database::clone('main', Database::MYSQLI, 'mysqli'),
            1
        ];
        yield 'PDO' => [
            'pdo',
            Database::clone('main', Database::PDO, 'pdo'),
            '1'
        ];
    }

    /**
     * @dataProvider provideEnginesForSelection
     */
    public static function testSelectAndFetch(
        string            $databaseIdentifier,
        DBEngineInterface $DBEngine,
        array             $expectedResults1,
        array             $expectedResults2
    ): void {
        EntitySimple::_test_clearTable($databaseIdentifier);
        EntitySimple::_test_createSample($databaseIdentifier);
        EntitySimple::_test_createSample2($databaseIdentifier);

        $queryIdentifier = $DBEngine->execute("SELECT * FROM sql_test_entity");

        self::assertSame(2, $DBEngine->resultsNb($queryIdentifier));
        self::assertSame($expectedResults1, $DBEngine->fetch($queryIdentifier));
        self::assertSame($expectedResults2, $DBEngine->fetch($queryIdentifier));
        self::assertNull($DBEngine->fetch($queryIdentifier));
    }

    /**
     * @dataProvider provideEnginesForInsert
     */
    public static function testInsertAndLastInsertId(
        string            $databaseIdentifier,
        DBEngineInterface $DBEngine,
        string|int $expected
    ): void {
        EntitySimple::_test_clearTable($databaseIdentifier);

        $DBEngine->execute("INSERT INTO sql_test_entity (name,active) VALUES('test_name','5')");

        self::assertSame($expected, $DBEngine->lastInsertId());
    }
}
