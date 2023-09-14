<?php

namespace tests\Architekt\DB;

use Architekt\DB\Database;
use Architekt\DB\Entity;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Motors\Mysql\MysqlSelect;
use Iterator;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\EntityWithOtherTable;
use tests\Architekt\DB\EntitySamples\EntitySimple;
use tests\Architekt\DB\EntitySamples\EntitySimple2;
use tests\Architekt\DB\EntitySamples\EntityWithCustomLabel;
use tests\Architekt\DB\EntitySamples\EntityWithDefaults;
use tests\Architekt\DB\EntitySamples\EntityWithCustomDatabase;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKey;
use tests\Architekt\DB\EntitySamples\EntityWithoutTable;
use tests\Architekt\DB\EntitySamples\EntityWithPrefix;
use tests\Architekt\DB\EntitySamples\EntityWithStranger;

final class EntityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        EntitySimple::_test_dropTable();
        EntityWithCustomPrimaryKey::_test_dropTable();
        EntityWithOtherTable::_test_dropTable();
        EntityWithStranger::_test_dropTable();
        parent::tearDown();
    }

    public function test__construct_withPrimaryReturnLoadedEntity(): void
    {
        $insertId = EntitySimple::_test_createRow("Test1", 1);

        $test = new EntitySimple($insertId);

        $this->assertTrue($test->_isLoaded());
        $this->assertEquals(
            $insertId,
            $test->_primary()
        );
        $this->assertEquals(
            "Test1",
            $test->_get('name')
        );
        $this->assertEquals(
            "1",
            $test->_get('active')
        );
    }

    public function test__construct_returnAsExpected(): void
    {
        $test = new EntitySimple();
        $this->assertFalse($test->_isLoaded());
        $this->assertEquals(
            [],
            $test->_get()
        );

        $insertId = EntitySimple::_test_createRow("Test1", 1);

        $test = new EntitySimple($insertId);

        $this->assertTrue($test->_isLoaded());
        $this->assertEquals(
            $insertId,
            $test->_primary()
        );
        $this->assertEquals(
            "Test1",
            $test->_get('name')
        );
        $this->assertEquals(
            "1",
            $test->_get('active')
        );
    }

    public function test_datatable_returnAsExpected(): void
    {
        $this->assertEquals(
            APPLICATION_MAIN_DATABASE,
            (new EntitySimple())->_database()
        );
        $this->assertEquals(
            'customdatabase',
            (new EntityWithCustomDatabase())->_database()
        );
    }

    public function test_table_returnAsExpected(): void
    {
        $this->assertEquals('sql_test_entity', (new EntitySimple())->_table());

        $entity = new EntityWithPrefix();
        $this->assertEquals('prefixed_sql_test_entity', $entity->_table());
        $this->assertEquals('sql_test_entity', $entity->_table(false));

        $this->expectException(MissingConfigurationException::class);
        (new EntityWithoutTable())->_table();
    }

    public function test_primary_returnAsExpected(): void
    {
        $this->assertNull((new EntitySimple())->_primary());

        $entityId = EntitySimple::_test_createRow('testName', 0);

        $entityLoaded = new EntitySimple($entityId);
        $this->assertEquals(
            $entityId,
            $entityLoaded->_primary()
        );
    }

    public function test_primaryKey_returnAsExpected(): void
    {
        $this->assertEquals(
            'id',
            (new EntitySimple())->_primaryKey()
        );
        $this->assertEquals(
            'uid',
            (new EntityWithCustomPrimaryKey())->_primaryKey()
        );
    }

    public function test_get_returnAsExpected(): void
    {
        $entitySimple = EntitySimple::_test_createSample();

        $this->assertNull($entitySimple->_get('fakeKey'));

        $this->assertEquals(
            'test1',
            $entitySimple->_get('name')
        );
        $this->assertEquals([
            'id' => $entitySimple->_primary(),
            'name' => 'test1',
            'active' => '1'
        ], $entitySimple->_get());
    }

    public function test_set_returnAsExpected(): void
    {
        $entitySimple = new EntitySimple();

        $entitySimple->_set('testKey', 'testValue');
        $this->assertSame('testValue', $entitySimple->_get('testKey'));

        $entitySimple = new EntitySimple();
        $entitySimple->_set('testKey');
        $this->assertNull($entitySimple->_get('testKey'));

        $entitySimple = (new EntitySimple())
            ->_set([
                'testKey1' => 'testValue1',
                'testKey2' => 'testValue2',
            ]);

        $this->assertSame(
            [
                'testKey1' => 'testValue1',
                'testKey2' => 'testValue2',
            ],
            $entitySimple->_get()
        );

        $entitySimpleLoaded = EntitySimple::_test_createSample();

        $entitySimple = (new EntitySimple())
            ->_set([
                $entitySimpleLoaded,
                'testKey2' => 'testValue2',
            ]);

        $this->assertSame(
            [
                'sql_test_entity_id' => $entitySimpleLoaded->_primary(),
                'testKey2' => 'testValue2',
            ],
            $entitySimple->_get()
        );


        $entitySimple = (new EntitySimple())
            ->_set([
                'keyName' => $entitySimpleLoaded,
                'testKey2' => 'testValue2',
            ]);

        $this->assertSame(
            [
                'keyName' => $entitySimpleLoaded->_primary(),
                'testKey2' => 'testValue2',
            ],
            $entitySimple->_get()
        );



        $entitySimple->_set($entitySimpleLoaded);
        $this->assertSame(
            $entitySimpleLoaded->_primary(),
            $entitySimple->_get('sql_test_entity_id')
        );

        $entitySimple = new EntitySimple();
        $entitySimpleLoaded = EntitySimple::_test_createSample();

        $entitySimple->_set(['name' => 'toto' , $entitySimpleLoaded]);
        $this->assertSame(
            $entitySimpleLoaded->_primary(),
            $entitySimple->_get('sql_test_entity_id')
        );
    }

    public function test_get_set_combinedArrayWillReturnExpectedValues(): void
    {
        $entity = (new EntitySimple())
            ->_set([
                'field1' => 'value1',
                'field2' => 'value2',
            ]);

        $this->assertSame([
            'field1' => 'value1',
            'field2' => 'value2',
        ], $entity->_get());

        $entity->_set([
            'field3' => 'value3',
            'field4' => 'value4',
        ]);
        $this->assertSame([
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
            'field4' => 'value4',
        ], $entity->_get());
    }

    public function test_get_set_combinedArrayAndSimpleWillReturnExpectedValues(): void
    {
        $entity = new EntitySimple();
        $entity->_set('field3', 'value3');
        $entity->_set([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);

        $this->assertSame([
            'field3' => 'value3',
            'field1' => 'value1',
            'field2' => 'value2',
        ], $entity->_get());
    }

    public function test_get_set_combinedSimpleAndArrayWillReturnExpectedValues(): void
    {
        $entity = new EntitySimple();
        $entity->_set('field1', 'value1');
        $entity->_set([
            'field2' => 'value2',
            'field3' => 'value3',
        ]);

        $this->assertSame([
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
        ], $entity->_get());
    }

    public function test_get_set_strangerWillReturnExternalFieldValue(): void
    {
        $entity = EntitySimple::_test_createSample();

        $entitySet = new EntitySimple();
        $entitySet->_set($entity);

        $this->assertSame($entity->_primary(), $entitySet->_get('sql_test_entity_id'));
    }

    public function test_diff_willReturnChangesValues(): void
    {
        $entity = new EntitySimple();
        $this->assertFalse($entity->_diff());
        $this->assertFalse($entity->_hasDiff());

        $entity->_set('field1', 'value1');
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertEquals(['field1'=>'value1'], $entity->_diff());

        $entity->_set('field1', null);
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertEquals(['field1'=>null], $entity->_diff());

        $entity->_set('field1', 'value1_2');
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertEquals(['field1'=>'value1_2'], $entity->_diff());

        $entity->_set('field2', 'value2');
        $this->assertNotFalse($entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertCount(2, $entity->_diff());
        $this->assertEquals(['field1'=>'value1_2','field2'=>'value2'], $entity->_diff());

        $entity = EntitySimple::_test_createSample();
        $this->assertFalse($entity->_diff());
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set('name', 'Toto');
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());

        $entity->_set('active', '0');
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(2, $entity->_diff());
    }

    public function test_change_setToOriginalValuesWillReturnNoChanges(): void
    {
        $entity = EntitySimple::_test_createSample();
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set([
            'name' => 'test1',
            'active' => '1',
        ]);
        $this->assertEquals(false, $entity->_hasDiff());
    }

    public function test_change_returnBackToIntCastedOriginalValueWillReturnChanges(): void
    {
        $entity = EntitySimple::_test_createSample();
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set([
            'name' => 'test1',
            'active' => '1',
        ]);
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set('active', 1);
        $this->assertEquals(true, $entity->_hasDiff());

    }

    public function test_change_originalValuesWillReturnNoChanges(): void
    {
        $entity = EntitySimple::_test_createSample();
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set('name', 'test1');
        $this->assertEquals(false, $entity->_hasDiff());

    }

    public function test_compare_willReturnExpectedValues(): void
    {
        $entity = EntitySimple::_test_createSample();

        $this->assertSame(
            [
                'tests\Architekt\DB\EntitySamples\EntitySimple',
                true,
                (string)$entity->_primary()
            ],
            $entity->_compare()
        );

        $entity = new EntitySimple();

        $this->assertSame(
            [
                'tests\Architekt\DB\EntitySamples\EntitySimple',
                false,
                null
            ],
            $entity->_compare()
        );
    }

    public function test_delete_willReturnFalseOnNotLoadedEntity(): void
    {
        $entity = new EntitySimple();
        $this->assertFalse($entity->_delete());
    }

    public function test_delete_willReturnTrueAndRemoveLoadedEntity(): void
    {
        $primary = EntitySimple::_test_createRow();
        $entity = new EntitySimple($primary);
        $this->assertTrue($entity->_isLoaded());
        $this->assertTrue($entity->_delete());
        $this->assertFalse($entity->_isLoaded());
        $this->assertFalse($entity->_diff());
        $this->assertCount(0, $entity->_get());

        $entity = new EntitySimple($primary);
        $this->assertFalse($entity->_isLoaded());
        $this->assertCount(0, $entity->_get());
    }

    public function test_delete_willBeLoadedOnTransactionRollback(): void
    {
        $primary = EntitySimple::_test_createRow();

        Database::engine()->startTransaction();

        $entity = new EntitySimple($primary);
        $entity->_delete();

        Database::engine()->rollbackTransaction();

        $entity = new EntitySimple($primary);
        $this->assertTrue($entity->_isLoaded());
    }

    public function test_setDefaults_willSetDefaultsOnEntityCreation(): void
    {
        $entity = new EntityWithDefaults();
        $this->assertSame('WriteNameHere', $entity->_get('name'));

        $primary = EntityWithDefaults::_test_createRow();
        $entity = new EntityWithDefaults($primary);
        $this->assertNull($entity->_get('name'));
    }

    public function testIsFieldValueUnique_willReturnAsExpected(): void
    {
        EntitySimple::_test_clearTable();

        $entityId1 = EntitySimple::_test_createRow(
            'test1',
            1
        );
        EntitySimple::_test_createRow(
            'test2',
            0
        );
        EntitySimple::_test_createRow(
            'test3',
            0
        );
        EntitySimple::_test_createRow(
            'test1',
            0
        );

        $entity = new EntitySimple();

        $this->assertTrue($entity->isFieldValueUnique('name', 'test4'));
        $this->assertFalse($entity->isFieldValueUnique('name', 'test3'));
        $this->assertTrue($entity->isFieldValueUnique('name', 'test3', ['active' => 1]));
        $this->assertFalse($entity->isFieldValueUnique('active', '1'));
        $this->assertTrue($entity->isFieldValueUnique('active', '2'));

        $entity = new EntitySimple($entityId1);
        $this->assertTrue($entity->isFieldValueUnique('active', '1'));
        $this->assertFalse($entity->isFieldValueUnique('name', 'test3'));
        $this->assertFalse($entity->isFieldValueUnique('name', 'test1'));
    }

    public function test_has_willReturnAsExpected(): void
    {
        $entity = new EntitySimple();
        $this->assertFalse($entity->_has('testField'));

        $entity->_set('testField', null);
        $this->assertTrue($entity->_has('testField'));

        $entity->_set('testField', 0);
        $this->assertTrue($entity->_has('testField'));

        $entity->_set('testField', false);
        $this->assertTrue($entity->_has('testField'));

        $entity->_set('testField', '');
        $this->assertTrue($entity->_has('testField'));

        $entity = EntitySimple::_test_createSample();
        $this->assertFalse($entity->_has('testField'));
        $this->assertTrue($entity->_has('name'));

    }

    /**
     * @dataProvider provide_isEqualTo_cases
     */
    public function test_isEqualTo_willReturnAsExpected(Entity $entity1, Entity $entity2, bool $expected): void
    {
        $this->assertEquals($expected, $entity1->_isEqualTo($entity2));
        $this->assertEquals($expected, $entity2->_isEqualTo($entity1));
    }

    public function test_isNull_willReturnAsExpected(): void
    {
        $entity = new EntitySimple();
        $this->assertFalse($entity->_isNull('testField'));

        $entity->_set('testField', null);
        $this->assertTrue($entity->_isNull('testField'));

        $entity = new EntitySimple(EntitySimple::_test_createRow());
        $this->assertTrue($entity->_isNull('name'));
    }

    public function test_isSameClass_willReturnAsExpected(): void
    {
        $entity1 = new EntitySimple();
        $entity2 = new EntitySimple2();
        $this->assertFalse($entity1->_isSameClass($entity2));

        $entity3 = new EntitySimple();
        $this->assertTrue($entity1->_isSameClass($entity3));
    }

    public function testLabel_willReturnAsExpected(): void
    {
        $entity = EntitySimple::_test_createSample();
        $this->assertSame('test1', $entity->label());

        $entity = EntityWithCustomLabel::_test_createSample();
        $this->assertSame('test1 is active', $entity->label());
    }

    public function test_forceLoaded(): void
    {
        $entity = new EntitySimple();
        $this->assertFalse($entity->_isLoaded());

        $entity = (new EntitySimple())->_forceLoaded();
        $this->assertTrue($entity->_isLoaded());
    }

    public function test_next_willReturnNextItemLoaded(): void
    {
        EntitySimple::_test_clearTable();
        $entity = new EntitySimple();
        $entity->_search();
        $this->assertFalse($entity->_next());
        $this->assertFalse($entity->_isLoaded());
        $this->assertCount(0, $entity->_get());

        EntitySimple::_test_createRow(
            'test1',
            1
        );

        EntitySimple::_test_createRow(
            'test2',
            0
        );

        $entity = new EntitySimple();
        $entity->_search()->orderAsc($entity->_primaryKey());

        $this->assertTrue($entity->_next());
        $this->assertTrue($entity->_isLoaded());
        $this->assertSame(
            ['id' => '1', 'name' => 'test1', 'active' => '1'],
            $entity->_get()
        );

        $this->assertTrue($entity->_next());
        $this->assertTrue($entity->_isLoaded());
        $this->assertSame(
            ['id' => '2', 'name' => 'test2', 'active' => '0'],
            $entity->_get()
        );

        $this->assertFalse($entity->_next());
        $this->assertFalse($entity->_isLoaded());
        $this->assertCount(0, $entity->_get());
    }

    public function test_search_mustReturnMysqlSelect(): void
    {
        $entity = new EntitySimple();
        $this->assertInstanceOf(MysqlSelect::class, $entity->_search());

        $entity = new EntitySimple();
        $this->assertInstanceOf(MysqlSelect::class, $entity->_initSearch());
    }


    public function test_primary_willReturnPrimaryValue(): void
    {
        $entityId = EntitySimple::_test_createRow();

        $entity = new EntitySimple($entityId);
        $this->assertSame((string)$entityId, $entity->_get('id'));
        $this->assertSame((string)$entityId, $entity->_primary());

        $entityId = EntityWithCustomPrimaryKey::_test_createRow();
        $entity = new EntityWithCustomPrimaryKey($entityId);
        $this->assertSame((string)$entityId, $entity->_get('uid'));
        $this->assertSame((string)$entityId, $entity->_primary());
    }

    public function test_primaryField_willReturnPrimaryField(): void
    {
        $entity = new EntitySimple();
        $this->assertSame('id', $entity->_primaryKey());

        $entity = new EntityWithCustomPrimaryKey();
        $this->assertSame('uid', $entity->_primaryKey());
    }

    public function test_results_willReturnEntitiesList(): void
    {
        EntitySimple::_test_clearTable();
        $entity = new EntitySimple();
        $entity->_search();
        $this->assertCount(0, $entity->_results());

        EntitySimple::_test_createRow(
            'test1',
            1
        );
        EntitySimple::_test_createRow(
            'test2',
            0
        );
        EntitySimple::_test_createRow(
            'test3',
            1
        );

        $entity->_initSearch();
        $results = $entity->_results();
        $this->assertCount(3, $results);
        $this->assertEquals(3, $entity->_resultsCount());

        $this->assertSame([1, 2, 3], array_keys($results));

        $this->assertSame(
            ['id' => '1', 'name' => 'test1', 'active' => '1'],
            $results[1]->_get()
        );
        $this->assertSame(
            ['id' => '2', 'name' => 'test2', 'active' => '0'],
            $results[2]->_get()
        );
        $this->assertSame(
            ['id' => '3', 'name' => 'test3', 'active' => '1'],
            $results[3]->_get()
        );

    }

    public function test__resultsToArray_willReturnArray(): void
    {
        EntitySimple::_test_clearTable();
        $entity = new EntitySimple();
        $entity->_search();
        $this->assertCount(0, $entity->_resultsToArray());

        EntitySimple::_test_createRow(
            'test1',
            1
        );
        EntitySimple::_test_createRow(
            'test2',
            0
        );
        EntitySimple::_test_createRow(
            'test3',
            1
        );

        $entity->_initSearch();
        $results = $entity->_resultsToArray();
        $this->assertCount(3, $results);

        $this->assertSame([1, 2, 3], array_keys($results));

        $this->assertSame(
            [
                1 => ['id' => '1', 'name' => 'test1', 'active' => '1'],
                2 => ['id' => '2', 'name' => 'test2', 'active' => '0'],
                3 => ['id' => '3', 'name' => 'test3', 'active' => '1'],
            ],
            $results
        );
    }

    public function test__resultsCount_willReturnAsExpected(): void
    {
        EntitySimple::_test_clearTable();
        $entity = new EntitySimple();
        $entity->_search();
        $this->assertSame(0, $entity->_resultsCount());

        EntitySimple::_test_createRow(
            'test1',
            1
        );
        EntitySimple::_test_createRow(
            'test2',
            0
        );
        EntitySimple::_test_createRow(
            'test3',
            1
        );

        $entity->_initSearch();
        $this->assertSame(3, $entity->_resultsCount());

        $entity->_initSearch()->filter('id', 5);
        $this->assertSame(0, $entity->_resultsCount());
    }

    public function test_hasToBeSaved_willReturnExpectedValue(): void
    {
        $entity = new Entity();
        $this->assertTrue($entity->_hasToBeSaved());

        $entity = EntitySimple::_test_createSample();
        $this->assertFalse($entity->_hasToBeSaved());

        $entity->_set('name', 'toto');
        $this->assertTrue($entity->_hasToBeSaved());
    }

    public function test_save_onLoadedEntityWillUpdateAsExpected(): void
    {
        $entityId = EntitySimple::_test_createRow();
        $entitySimple = new EntitySimple($entityId);

        self::assertTrue($entitySimple
            ->_set([
                'name' => 'update',
                'active' => '2',
            ])
            ->_save());

        self::assertSame((string)$entityId, $entitySimple->_primary());
        self::assertTrue($entitySimple->_isLoaded());

        $entitySimple = new EntitySimple($entityId);
        self::assertSame([
            'id' => (string)$entityId,
            'name' => 'update',
            'active' => '2',
        ], $entitySimple->_get());
    }

    public function test_save_onNewEntityWillInsertAsExpected(): void
    {
        EntitySimple::_test_clearTable();

        $entitySimple = new EntitySimple();
        $entitySimple
            ->_set([
                'name' => 'update',
                'active' => '2',
            ]);

        $this->assertTrue($entitySimple->_save());
        $this->assertTrue($entitySimple->_isLoaded());
        $this->assertSame(1, $entitySimple->_primary());

        $entitySimple = new EntitySimple(1);
        $this->assertSame([
            'id' => '1',
            'name' => 'update',
            'active' => '2',
        ], $entitySimple->_get());

    }

    public static function provide_isEqualTo_cases(): Iterator
    {
        $entity1 = EntitySimple::_test_createSample();
        $entity2 = EntitySimple::_test_createSample();
        $entity3 = clone $entity1;
        $entity4 = clone $entity1;
        $entity4->_set('fakeField', 1);

        yield [new EntitySimple(), new EntitySimple2(), false];
        yield [new EntitySimple(), new EntitySimple(), true];

        yield [$entity1, $entity2, false];
        yield [$entity1, $entity3, true];
        yield [$entity1, $entity4, true];
        yield [$entity1, new EntitySimple(), false];
    }
}
