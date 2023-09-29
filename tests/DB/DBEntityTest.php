<?php

namespace tests\Architekt\DB;

use Architekt\DB\DBConnexion;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Interfaces\DBEntityInterface;
use Architekt\DB\Translators\DBEntityRecordSearchTranslator;
use Iterator;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\DBEntitySimple;
use tests\Architekt\DB\EntitySamples\DBEntitySimple2;
use tests\Architekt\DB\EntitySamples\DBEntityWithCustomDatabase;
use tests\Architekt\DB\EntitySamples\DBEntityWithCustomLabel;
use tests\Architekt\DB\EntitySamples\DBEntityWithCustomPrimaryKey;
use tests\Architekt\DB\EntitySamples\DBEntityWithDefaults;
use tests\Architekt\DB\EntitySamples\DBEntityWithoutTable;
use tests\Architekt\DB\EntitySamples\DBEntityWithPrefix;
use tests\Architekt\DB\EntitySamples\EntitySimple;

final class DBEntityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        /* DBEntitySimple::_test_dropTable();
         DBEntityWithCustomPrimaryKey::_test_dropTable();
         DBEntityWithOtherTable::_test_dropTable();
         DBEntityWithStranger::_test_dropTable();*/
        parent::tearDown();
    }

    public function test__construct_withPrimaryReturnLoadedEntity(): void
    {
        $testId = DBEntitySimple::_test_createRow("test1", 1);

        $test = new DBEntitySimple($testId);
        $this->assertTrue($test->_isLoaded());

        $this->assertEquals(
            $testId,
            $test->_primary()
        );
        $this->assertEquals(
            "test1",
            $test->_get('name')
        );
        $this->assertEquals(
            1,
            $test->_get('active')
        );
    }

    public function test__construct_returnAsExpected(): void
    {
        $test = new DBEntitySimple();
        $this->assertFalse($test->_isLoaded());
        $this->assertEquals([], $test->_get());

        $test = DBEntitySimple::_test_createSample();

        $this->assertTrue($test->_isLoaded());

        $this->assertEquals(
            "test1",
            $test->_get('name')
        );
        $this->assertEquals(
            1,
            $test->_get('active')
        );
    }

    public function test_datatable_returnAsExpected(): void
    {
        $this->assertEquals(
            APPLICATION_MAIN_DATABASE,
            (new DBEntitySimple())->_database()
        );
        $this->assertEquals(
            'customdatabase',
            (new DBEntityWithCustomDatabase())->_database()
        );
    }

    public function test_table_returnAsExpected(): void
    {
        $this->assertEquals('sql_test_entity', (new DBEntitySimple())->_table());

        $entity = new DBEntityWithPrefix();
        $this->assertEquals('prefixed_sql_test_entity', $entity->_table());
        $this->assertEquals('sql_test_entity', $entity->_table(false));

        $this->expectException(MissingConfigurationException::class);
        (new DBEntityWithoutTable())->_table();
    }

    public function test_primary_returnAsExpected(): void
    {
        $this->assertNull((new DBEntitySimple())->_primary());

        $entityId = DBEntitySimple::_test_createRow('testName', 0);

        $entityLoaded = new DBEntitySimple($entityId);
        $this->assertEquals(
            $entityId,
            $entityLoaded->_primary()
        );
    }

    public function test_primaryKey_returnAsExpected(): void
    {
        $this->assertEquals(
            'id',
            (new DBEntitySimple())->_primaryKey()
        );
        $this->assertEquals(
            'uid',
            (new DBEntityWithCustomPrimaryKey())->_primaryKey()
        );
    }

    public function test_get_returnAsExpected(): void
    {
        $entitySimple = DBEntitySimple::_test_createSample();

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
        $entitySimple = new DBEntitySimple();

        $entitySimple->_set('testKey', 'testValue');
        $this->assertSame('testValue', $entitySimple->_get('testKey'));

        $entitySimple = new DBEntitySimple();
        $entitySimple->_set('testKey');
        $this->assertNull($entitySimple->_get('testKey'));

        $entitySimple = (new DBEntitySimple())
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

        $entitySimpleLoaded = DBEntitySimple::_test_createSample();

        $entitySimple = (new DBEntitySimple())
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


        $entitySimple = (new DBEntitySimple())
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

        $entitySimple = new DBEntitySimple();
        $entitySimpleLoaded = DBEntitySimple::_test_createSample();

        $entitySimple->_set(['name' => 'toto', $entitySimpleLoaded]);
        $this->assertSame(
            $entitySimpleLoaded->_primary(),
            $entitySimple->_get('sql_test_entity_id')
        );
    }

    public function test_get_set_combinedArrayWillReturnExpectedValues(): void
    {
        $entity = (new DBEntitySimple())
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
        $entity = new DBEntitySimple();
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
        $entity = new DBEntitySimple();
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
        $entity = DBEntitySimple::_test_createSample();

        $entitySet = new DBEntitySimple();
        $entitySet->_set($entity);

        $this->assertSame($entity->_primary(), $entitySet->_get('sql_test_entity_id'));
    }

    public function test_diff_willReturnChangesValues(): void
    {
        $entity = new DBEntitySimple();
        $this->assertFalse($entity->_diff());
        $this->assertFalse($entity->_hasDiff());

        $entity->_set('field1', 'value1');
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertEquals(['field1' => 'value1'], $entity->_diff());

        $entity->_set('field1', null);
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertEquals(['field1' => null], $entity->_diff());

        $entity->_set('field1', 'value1_2');
        $this->assertNotFalse($entity->_diff());
        $this->assertCount(1, $entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertEquals(['field1' => 'value1_2'], $entity->_diff());

        $entity->_set('field2', 'value2');
        $this->assertNotFalse($entity->_diff());
        $this->assertEquals(true, $entity->_hasDiff());
        $this->assertCount(2, $entity->_diff());
        $this->assertEquals(['field1' => 'value1_2', 'field2' => 'value2'], $entity->_diff());

        $entity = DBEntitySimple::_test_createSample();
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
        $entity = DBEntitySimple::_test_createSample();
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set([
            'name' => 'test1',
            'active' => 1,
        ]);
        $this->assertEquals(false, $entity->_hasDiff());
    }

    public function test_change_returnBackToIntCastedOriginalValueWillReturnChanges(): void
    {
        $entity = DBEntitySimple::_test_createSample();

        $entity->_set('active', "1");
        $this->assertEquals(true, $entity->_hasDiff());

    }

    public function test_change_originalValuesWillReturnNoChanges(): void
    {
        $entity = DBEntitySimple::_test_createSample();
        $this->assertEquals(false, $entity->_hasDiff());

        $entity->_set('name', 'test1');
        $this->assertEquals(false, $entity->_hasDiff());

    }

    public function test_compare_willReturnExpectedValues(): void
    {
        $entity = DBEntitySimple::_test_createSample();

        $this->assertSame(
            [
                'tests\Architekt\DB\EntitySamples\DBEntitySimple',
                true,
                $entity->_primary()
            ],
            $entity->_compare()
        );

        $entity = new DBEntitySimple();

        $this->assertSame(
            [
                'tests\Architekt\DB\EntitySamples\DBEntitySimple',
                false,
                null
            ],
            $entity->_compare()
        );
    }

    public function test_delete_willReturnFalseOnNotLoadedEntity(): void
    {
        $entity = new DBEntitySimple();
        $this->assertFalse($entity->_delete());
    }

    public function test_delete_willReturnTrueAndRemoveLoadedEntity(): void
    {
        $primary = DBEntitySimple::_test_createRow();
        $entity = new DBEntitySimple($primary);
        $this->assertTrue($entity->_isLoaded());
        $this->assertTrue($entity->_delete());
        $this->assertFalse($entity->_isLoaded());
        $this->assertFalse($entity->_diff());
        $this->assertCount(0, $entity->_get());

        $entity = new DBEntitySimple($primary);
        $this->assertFalse($entity->_isLoaded());
        $this->assertCount(0, $entity->_get());
    }

    public function test_delete_willBeLoadedOnTransactionRollback(): void
    {
        $primary = DBEntitySimple::_test_createRow();

        DBConnexion::get()->transactionStart();

        $entity = new DBEntitySimple($primary);
        $entity->_delete();

        DBConnexion::get()->transactionRollBack();

        $entity = new DBEntitySimple($primary);
        $this->assertTrue($entity->_isLoaded());
    }

    public function test_setDefaults_willSetDefaultsOnEntityCreation(): void
    {
        $entity = new DBEntityWithDefaults();
        $this->assertSame('WriteNameHere', $entity->_get('name'));

        $primary = DBEntityWithDefaults::_test_createRow();
        $entity = new DBEntityWithDefaults($primary);
        $this->assertNull($entity->_get('name'));
    }

    public function testIsFieldValueUnique_willReturnAsExpected(): void
    {
        DBEntitySimple::_test_clearTable();

        $entityId1 = DBEntitySimple::_test_createRow(
            'test1',
            1
        );
        DBEntitySimple::_test_createRow(
            'test2',
            0
        );
        DBEntitySimple::_test_createRow(
            'test3',
            0
        );
        DBEntitySimple::_test_createRow(
            'test1',
            0
        );

        $entity = new DBEntitySimple();

        $this->assertTrue($entity->isFieldValueUnique('name', 'test4'));
        $this->assertFalse($entity->isFieldValueUnique('name', 'test3'));
        $this->assertTrue($entity->isFieldValueUnique('name', 'test3', ['active' => 1]));
        $this->assertFalse($entity->isFieldValueUnique('active', '1'));
        $this->assertTrue($entity->isFieldValueUnique('active', '2'));

        $entity = new DBEntitySimple($entityId1);
        $this->assertTrue($entity->isFieldValueUnique('active', '1'));
        $this->assertFalse($entity->isFieldValueUnique('name', 'test3'));
        $this->assertFalse($entity->isFieldValueUnique('name', 'test1'));
    }

    public function test_has_willReturnAsExpected(): void
    {
        $entity = new DBEntitySimple();
        $this->assertFalse($entity->_has('testField'));

        $entity->_set('testField', null);
        $this->assertTrue($entity->_has('testField'));

        $entity->_set('testField', 0);
        $this->assertTrue($entity->_has('testField'));

        $entity->_set('testField', false);
        $this->assertTrue($entity->_has('testField'));

        $entity->_set('testField', '');
        $this->assertTrue($entity->_has('testField'));

        $entity = DBEntitySimple::_test_createSample();
        $this->assertFalse($entity->_has('testField'));
        $this->assertTrue($entity->_has('name'));

    }

    /**
     * @dataProvider provide_isEqualTo_cases
     */

    public function test_isEqualTo_willReturnAsExpected(DBEntityInterface $entity1, DBEntityInterface $entity2, bool $expected): void
    {
        $this->assertEquals($expected, $entity1->_isEqualTo($entity2));
        $this->assertEquals($expected, $entity2->_isEqualTo($entity1));
    }

    public function test_isNull_willReturnAsExpected(): void
    {
        $entity = new DBEntitySimple();
        $this->assertFalse($entity->_isNull('testField'));

        $entity->_set('testField');
        $this->assertTrue($entity->_isNull('testField'));

        $entity = new DBEntitySimple(DBEntitySimple::_test_createRow());
        $this->assertTrue($entity->_isNull('name'));
    }

    public function test_isSameClass_willReturnAsExpected(): void
    {
        $entity1 = new DBEntitySimple();
        $entity2 = new DBEntitySimple2();
        $this->assertFalse($entity1->_isSameClass($entity2));

        $entity3 = new DBEntitySimple();
        $this->assertTrue($entity1->_isSameClass($entity3));
    }

    public function testLabel_willReturnAsExpected(): void
    {
        $entity = DBEntitySimple::_test_createSample();
        $this->assertSame('test1', $entity->label());

        $entity = DBEntityWithCustomLabel::_test_createSample();
        $this->assertSame('test1 is active', $entity->label());
    }

    public function test_forceLoaded(): void
    {
        $entity = new DBEntitySimple();
        $this->assertFalse($entity->_isLoaded());

        $entity = (new DBEntitySimple())->_forceLoaded();
        $this->assertTrue($entity->_isLoaded());
    }

    public function test_next_willReturnNextItemLoaded(): void
    {
        DBEntitySimple::_test_clearTable();
        $entity = new DBEntitySimple();
        $entity->_search();
        $this->assertFalse($entity->_next());
        $this->assertFalse($entity->_isLoaded());
        $this->assertCount(0, $entity->_get());

        DBEntitySimple::_test_createRow(
            'test1',
            1
        );

        DBEntitySimple::_test_createRow(
            'test2',
            0
        );

        $entity = new DBEntitySimple();
        $entity->_search()->orderAsc($entity);

        $this->assertTrue($entity->_next());
        $this->assertTrue($entity->_isLoaded());
        $this->assertSame(
            ['id' => 1, 'name' => 'test1', 'active' => 1],
            $entity->_get()
        );

        $this->assertTrue($entity->_next());
        $this->assertTrue($entity->_isLoaded());
        $this->assertSame(
            ['id' => 2, 'name' => 'test2', 'active' => 0],
            $entity->_get()
        );

        $this->assertFalse($entity->_next());
        $this->assertFalse($entity->_isLoaded());
        $this->assertCount(0, $entity->_get());
    }

    public function test_search_mustReturnDBEntityRecordSearchTranslator(): void
    {
        $entity = new DBEntitySimple();
        $this->assertInstanceOf(DBEntityRecordSearchTranslator::class, $entity->_search());

        $entity = new DBEntitySimple();
        $this->assertInstanceOf(DBEntityRecordSearchTranslator::class, $entity->_initSearch());
    }

    public function test_primary_willReturnPrimaryValue(): void
    {
        $entityId = DBEntitySimple::_test_createRow();

        $entity = new DBEntitySimple($entityId);
        $this->assertSame($entityId, $entity->_get('id'));
        $this->assertSame($entityId, $entity->_primary());

        $entityId = DBEntityWithCustomPrimaryKey::_test_createRow();
        $entity = new DBEntityWithCustomPrimaryKey($entityId);
        $this->assertSame($entityId, $entity->_get('uid'));
        $this->assertSame($entityId, $entity->_primary());
    }

    public function test_primaryField_willReturnPrimaryField(): void
    {
        $entity = new DBEntitySimple();
        $this->assertSame('id', $entity->_primaryKey());

        $entity = new DBEntityWithCustomPrimaryKey();
        $this->assertSame('uid', $entity->_primaryKey());
    }

    public function test_results_willReturnEntitiesList(): void
    {
        DBEntitySimple::_test_clearTable();

        $entity = new DBEntitySimple();
        $entity->_search();
        $this->assertCount(0, $entity->_results());

        DBEntitySimple::_test_createRow(
            'test1',
            1
        );
        DBEntitySimple::_test_createRow(
            'test2',
            0
        );
        DBEntitySimple::_test_createRow(
            'test3',
            1
        );

        $entity->_initSearch();
        //echo str_repeat('#',70);
        $results = $entity->_results();
        $this->assertCount(3, $results);
        $this->assertEquals(3, $entity->_resultsCount());

        $this->assertSame([1, 2, 3], array_keys($results));

        $this->assertSame(
            ['id' => 1, 'name' => 'test1', 'active' => 1],
            $results[1]->_get()
        );
        $this->assertSame(
            ['id' => 2, 'name' => 'test2', 'active' => 0],
            $results[2]->_get()
        );
        $this->assertSame(
            ['id' => 3, 'name' => 'test3', 'active' => 1],
            $results[3]->_get()
        );

    }

    public function test__resultsToArray_willReturnArray(): void
    {
        DBEntitySimple::_test_clearTable();
        $entity = new DBEntitySimple();
        $entity->_search();
        $this->assertCount(0, $entity->_resultsToArray());

        DBEntitySimple::_test_createRow(
            'test1',
            1
        );
        DBEntitySimple::_test_createRow(
            'test2',
            0
        );
        DBEntitySimple::_test_createRow(
            'test3',
            1
        );

        $entity->_initSearch();
        $results = $entity->_resultsToArray();
        $this->assertCount(3, $results);

        $this->assertSame([1, 2, 3], array_keys($results));

        $this->assertSame(
            [
                1 => ['id' => 1, 'name' => 'test1', 'active' => 1],
                2 => ['id' => 2, 'name' => 'test2', 'active' => 0],
                3 => ['id' => 3, 'name' => 'test3', 'active' => 1],
            ],
            $results
        );
    }

    public function test__resultsCount_willReturnAsExpected(): void
    {
        DBEntitySimple::_test_clearTable();
        $entity = new DBEntitySimple();
        $entity->_search();
        $this->assertSame(0, $entity->_resultsCount());

        DBEntitySimple::_test_createRow(
            'test1',
            1
        );
        DBEntitySimple::_test_createRow(
            'test2',
            0
        );
        DBEntitySimple::_test_createRow(
            'test3',
            1
        );

        $entity->_initSearch();
        $this->assertSame(3, $entity->_resultsCount());

        $entity->_initSearch()->and($entity, 5);
        $this->assertSame(0, $entity->_resultsCount());
    }

    public function test_hasToBeSaved_willReturnExpectedValue(): void
    {
        $entity = new DBEntitySimple();
        $this->assertTrue($entity->_hasToBeSaved());

        $entity = DBEntitySimple::_test_createSample();
        $this->assertFalse($entity->_hasToBeSaved());

        $entity->_set('name', 'toto');
        $this->assertTrue($entity->_hasToBeSaved());
    }

    public function test_save_onLoadedEntityWillUpdateAsExpected(): void
    {
        $entityId = DBEntitySimple::_test_createRow();
        $entitySimple = new DBEntitySimple($entityId);

        self::assertTrue(
            $entitySimple
                ->_set([
                    'name' => 'update',
                    'active' => 2,
                ])
                ->_save()
        );

        self::assertSame($entityId, $entitySimple->_primary());
        self::assertTrue($entitySimple->_isLoaded());
        $entitySimple = new DBEntitySimple($entityId);
        self::assertSame($entityId, $entitySimple->_primary());

        self::assertSame([
            'id' => (int)$entityId,
            'name' => 'update',
            'active' => 2,
        ], $entitySimple->_get());
    }


    public function test_save_onNewEntityWillInsertAsExpected(): void
    {
        DBEntitySimple::_test_clearTable();

        $entitySimple = new DBEntitySimple();
        $entitySimple
            ->_set([
                'name' => 'update',
                'active' => 2,
            ]);

        $this->assertTrue($entitySimple->_save());
        $this->assertTrue($entitySimple->_isLoaded());
        $this->assertSame(1, $entitySimple->_primary());

        $entitySimple = new DBEntitySimple(1);
        $this->assertSame([
            'id' => 1,
            'name' => 'update',
            'active' => 2,
        ], $entitySimple->_get());

    }

    public static function provide_isEqualTo_cases(): Iterator
    {
        $entity1 = DBEntitySimple::_test_createSample();
        $entity2 = DBEntitySimple::_test_createSample();
        $entity3 = clone $entity1;
        $entity4 = clone $entity1;
        $entity4->_set('fakeField', 1);

        yield [new DBEntitySimple(), new DBEntitySimple2(), false];
        yield [new DBEntitySimple(), new DBEntitySimple(), true];

        yield [$entity1, $entity2, false];
        yield [$entity1, $entity3, true];
        yield [$entity1, $entity4, true];
        yield [$entity1, new DBEntitySimple(), false];
    }
}
