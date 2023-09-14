<?php

namespace tests\Architekt\DB\Motors;

use Architekt\DB\Database;
use Architekt\DB\EntityInterface;
use Architekt\DB\Exceptions\InvalidParameterException;
use Architekt\DB\Motors\Mysql\MysqlSelect;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\EntitySimple;
use tests\Architekt\DB\EntitySamples\EntityWithCustomDatabase;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKey;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKeyAndPrefix;
use tests\Architekt\DB\EntitySamples\EntityWithOtherTable;
use tests\Architekt\DB\EntitySamples\EntityWithStranger;

class MysqlSelectTest extends TestCase
{
    private static array $filters = [
        ['=', 'filter'],
        ['!=', 'filterNot'],
        ['>', 'filterGreater'],
        ['>=', 'filterGreaterOrEqual'],
        ['<', 'filterLess'],
        ['<=', 'filterLessOrEqual']
    ];


    public function test_order()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            . 'ORDER BY `sql_test_entity`.`name` ASC',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->orderAsc('name')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            . 'ORDER BY `table_other_entity`.`name` ASC, `sql_test_entity`.`active` ASC',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->orderAsc(new EntityWithOtherTable(), 'name')
                ->orderAsc('active')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            . 'ORDER BY `sql_test_entity`.`name` DESC',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->orderDesc('name')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            . 'ORDER BY `table_other_entity`.`name` DESC, `sql_test_entity`.`active` DESC',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->orderDesc(new EntityWithOtherTable(), 'name')
                ->orderDesc('active')
                ->build()
        );

        $entityWithOtherTable = new EntityWithOtherTable();
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            . 'ORDER BY `table_other_entity`.`name` ASC, `sql_test_entity`.`active` DESC',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->orderAsc($entityWithOtherTable, 'name')
                ->orderDesc('active')
                ->build()
        );
    }

    public function test_select()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.`field` FROM `sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->select('field')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.`field` AS "myField" FROM `sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->select('field', 'myField')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.`field1`, `sql_test_entity`.`field2` FROM `sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->select('field1')
                ->select('field2')
                ->build()
        );

        $entityWithOtherTable = new EntityWithOtherTable();
        $this->assertEquals(
            'SELECT `sql_test_entity`.`field1`, `table_other_entity`.`field2`, `table_other_entity`.`field2` AS "RealField", `sql_test_entity`.`field3` FROM `sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->select('field1')
                ->select($entityWithOtherTable, 'field2')
                ->select($entityWithOtherTable, 'field2', 'RealField')
                ->select(new EntitySimple(), 'field3')
                ->build()
        );

    }

    public function test_withDifferentDatabase()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `customdatabase`.`sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntityWithCustomDatabase()
            ))
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->build()
        );
    }

    public function test_limit()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` LIMIT 0,15',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->limit(15)
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` LIMIT 10,15',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->limit(15, 10)
                ->build()
        );
    }


    public function test_between()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`active` BETWEEN "0" AND "2"',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->between('active', 0, 2)
                ->build()
        );


        $entityWithOtherTable = new EntityWithOtherTable();
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND '
            . '`sql_test_entity`.`active` BETWEEN "0" AND "2" '
            . 'AND `table_other_entity`.`otherField` BETWEEN "5" AND "10"',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->between('active', 0, 2)
                ->between($entityWithOtherTable, 'otherField', 5, 10)
                ->build()
        );
    }


    public static function provideFiltersWithOneParameter(): \Iterator
    {
        $entitySimpleNotLoaded = new EntitySimple();
        $entityStrangerNotLoaded = new EntityWithStranger();
        $entityId = EntitySimple::_test_createRow();
        $entitySimple = new EntitySimple($entityId);

        yield 'array' => [
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key1`%1$s"test1" AND `sql_test_entity`.`key2`%1$s"test2"',
            $entitySimpleNotLoaded,
            ['key1' => 'test1', 'key2' => 'test2'],
        ];
        yield 'arrayWithEntity' => [
            sprintf('SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`id`%%1$s"%s" AND `sql_test_entity`.`key2`%%1$s"test2"',$entityId),
            $entitySimpleNotLoaded,
            [$entitySimple, 'key2' => 'test2'],
        ];
        yield 'entityLoaded' => [
            sprintf('SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity_stranger`.`sql_test_entity_id`%%s"%d"', $entityId),
            $entityStrangerNotLoaded,
            $entitySimple,
        ];
        yield 'entitySame' => [
            sprintf('SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`id`%%s"%d"', $entityId),
            $entitySimpleNotLoaded,
            $entitySimple,
        ];
    }

    public static function provideFiltersWithTwoParameters(): \Iterator
    {
        $entitySimpleNotLoaded = new EntitySimple();
        $entityStrangerNotLoaded = new EntityWithStranger();
        $entityOtherNotLoaded = new EntityWithOtherTable();

        $entityId = EntitySimple::_test_createRow();
        $entitySimple = new EntitySimple($entityId);
        $entityId2 = EntityWithOtherTable::_test_createRow();
        $entityOther = new EntityWithOtherTable($entityId2);

        yield 'simple' => [
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key`%s"value"',
            $entitySimpleNotLoaded,
            'key',
            'value'
        ];
        yield 'simpleZero' => [
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key`%s"0"',
            $entitySimpleNotLoaded,
            'key',
            0
        ];
        yield 'entityLoaded' => [
            sprintf('SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `table_other_entity`.`id`%%s"%d"', $entityId),
            $entityStrangerNotLoaded,
            $entityOther,
            $entitySimple
        ];
        yield 'entityLoadedRevert' => [
            sprintf('SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity`.`id`%%s"%d"', $entityId2),
            $entityStrangerNotLoaded,
            $entitySimple,
            $entityOther
        ];
        yield 'entityNotLoaded' => [
            'SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity`.`id`%s`table_other_entity`.`id`',
            $entityStrangerNotLoaded,
            $entitySimple,
            $entityOtherNotLoaded
        ];
    }

    public static function provideFiltersWithThreeParameters(): \Iterator
    {
        $entityId = EntitySimple::_test_createRow();
        $entitySimple = new EntitySimple($entityId);
        $entityStrangerNotLoaded = new EntityWithStranger();
        $entityOtherNotLoaded = new EntityWithOtherTable();

        yield 'firstEntityWithField' => [
            sprintf('SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity`.`fieldChosen`%%s"%d"', $entityId),
            $entityStrangerNotLoaded,
            $entitySimple,
            'fieldChosen',
            $entityId
        ];

        yield 'firstEntityWithFieldWithLoaded' => [
            sprintf('SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity`.`fieldChosen`%%s"%d"', $entityId),
            $entityStrangerNotLoaded,
            $entitySimple,
            'fieldChosen',
            $entitySimple
        ];

        yield 'secondEntityWithField' => [
            'SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity`.`id`%s`table_other_entity`.`testOther`',
            $entityStrangerNotLoaded,
            $entitySimple,
            $entityOtherNotLoaded,
            'testOther'
        ];
    }

    public static function provideFiltersWithFourParameters()
    {
        $entityId = EntitySimple::_test_createRow();
        $entitySimple = new EntitySimple($entityId);
        $entityStrangerNotLoaded = new EntityWithStranger();
        $entityOtherNotLoaded = new EntityWithOtherTable();

        yield 'entitiesWithFields' => [
            'SELECT `sql_test_entity_stranger`.* FROM `sql_test_entity_stranger` WHERE 1 AND `sql_test_entity`.`testSimple`%s`table_other_entity`.`testOther`',
            $entityStrangerNotLoaded,
            $entitySimple,
            'testSimple',
            $entityOtherNotLoaded,
            'testOther'
        ];
    }

    /**
     * @dataProvider provideFiltersWithOneParameter
     */
    public function test_filterWithOneParameter(string $expected, EntityInterface $entity, mixed $arg1)
    {
        foreach (self::$filters as $case) {
            $this->assertEquals(
                sprintf($expected, $case[0]),
                (new MysqlSelect(
                    Database::engine()->motor(),
                    $entity
                ))
                    ->{$case[1]}($arg1)
                    ->build()
            );
        }
    }

    /**
     * @dataProvider provideFiltersWithTwoParameters
     */
    public function test_filterWithTwoParameters(string $expected, EntityInterface $entity, mixed $arg1, mixed $arg2)
    {
        foreach (self::$filters as $case) {
            $this->assertEquals(
                sprintf($expected, $case[0]),
                (new MysqlSelect(
                    Database::engine()->motor(),
                    $entity
                ))
                    ->{$case[1]}($arg1, $arg2)
                    ->build()
            );
        }
    }

    /**
     * @dataProvider provideFiltersWithThreeParameters
     */
    public function test_filterWithThreeParameters(string $expected, EntityInterface $entity, mixed $arg1, mixed $arg2, mixed $arg3)
    {
        foreach (self::$filters as $case) {
            $this->assertEquals(
                sprintf($expected, $case[0]),
                (new MysqlSelect(
                    Database::engine()->motor(),
                    $entity
                ))
                    ->{$case[1]}($arg1, $arg2, $arg3)
                    ->build()
            );
        }
    }

    /**
     * @dataProvider provideFiltersWithFourParameters
     */
    public function test_filterWithFourParameters(string $expected, EntityInterface $entity, mixed $arg1, mixed $arg2, mixed $arg3, mixed $arg4)
    {
        foreach (self::$filters as $case) {
            $this->assertEquals(
                sprintf($expected, $case[0]),
                (new MysqlSelect(
                    Database::engine()->motor(),
                    $entity
                ))
                    ->{$case[1]}($arg1, $arg2, $arg3, $arg4)
                    ->build()
            );
        }
    }


    public function test_filterWillReturnExceptionWhenNoParameter()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filter()
            ->build();
    }

    public function test_filterWillReturnExceptionWhenFirstEntityNotLoaded()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filter(new EntitySimple())
            ->build();
    }

    public function test_filterWillReturnExceptionWhenFourParameterAndThirdParameterNotEntity()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filter(new EntitySimple(), 'testField', 'errorParam', 'testField2')
            ->build();
    }

    public function test_filterWillReturnExceptionWhenFieldWithNoSecondParameter()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filter('testField')
            ->build();
    }


    public function test_filterWillNullParameters()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key` IS NULL',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filter('key', null)
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key` IS NOT NULL',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filterNot('key', null)
                ->build()
        );
    }

    public function test_filterOnArray(): void
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key` IN ("value1","value2")',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filter('key', ['value1','value2'])
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key` NOT IN ("value1","value2")',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filterNot('key', ['value1','value2'])
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key`!="value1"',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filterNot('key', ['value1'])
                ->build()
        );


        $entitySimple = EntitySimple::_test_createSample();

        $this->assertEquals(
            sprintf('SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key` NOT IN ("value1","%s")',$entitySimple->_primary()),
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filterNot('key', ['value1',$entitySimple])
                ->build()
        );
    }


    public function test_filterNotWillReturnExceptionWhenNoParameter()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filterNot()
            ->build();
    }

    public function test_filterNotWillReturnExceptionWhenFirstEntityNotLoaded()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filterNot(new EntitySimple())
            ->build();
    }

    public function test_filterNotWillReturnExceptionWhenFourParameterAndThirdParameterNotEntity()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filterNot(new EntitySimple(), 'testField', 'errorParam', 'testField2')
            ->build();
    }

    public function test_filterNotWillReturnExceptionWhenFieldWithNoSecondParameter()
    {
        $this->expectException(InvalidParameterException::class);

        (new MysqlSelect(
            Database::engine()->motor(),
            new EntityWithStranger()
        ))
            ->filter('testField')
            ->build();
    }

    /*public function test_or(): void
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key`="value" OR (`sql_test_entity`.`key2`!="value2")',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filter('key', 'value')
                ->startOr()
                ->filterNot('key2', 'value2')
                ->endOr()
                ->build()
        );
    }*/

    public function testSelectWithFilterAndFilterNot()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key`="value" AND `sql_test_entity`.`key2`!="value2"',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filter('key', 'value')
                ->filterNot('key2', 'value2')
                ->build()
        );

    }


    public function test_filterOr(): void
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`key`="value" OR `sql_test_entity`.`key2`="value2"',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filter('key', 'value')
                ->filterOr('key2', 'value2')
                ->build()
        );
    }

    public function test_brackets(): void
    {

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
                        .'WHERE 1 AND (`sql_test_entity`.`key`="value" OR `sql_test_entity`.`key2`="value2")',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->ob()
                ->filter('key', 'value')
                ->filterOr('key2', 'value2')
                ->build()
        );


        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            .'WHERE 1 AND ((`sql_test_entity`.`key`="value" ) OR (`sql_test_entity`.`key2`="value2"))',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->ob()
                    ->ob()
                        ->filter('key', 'value')
                    ->cb()
                    ->ob()
                        ->filterOr('key2', 'value2')

                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND ((`sql_test_entity`.`key`="value" OR `sql_test_entity`.`key2`="value2" ) AND (`sql_test_entity`.`key3`="value3" OR `sql_test_entity`.`key4` IN ("value4-1","value4-2")))',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->ob()
                    ->ob()
                        ->filter('key', 'value')
                        ->filterOr('key2', 'value2')
                    ->cb()
                    ->ob()
                    ->filter('key3', 'value3')
                    ->filterOr('key4', ['value4-1','value4-2'])
                ->build()
        );



        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            .'WHERE 1 AND (`sql_test_entity`.`key`="value" OR `sql_test_entity`.`key2`="value2" ) AND `sql_test_entity`.`key`!="value3"',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->ob()
                ->filter('key', 'value')
                ->filterOr('key2', 'value2')
                ->cb()
                ->filterNot('key', 'value3')

                ->build()
        );
    }

    public function testSelectWithLimitAndFilter(): void
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`fieldName`="fieldValue" LIMIT 0,10',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->limit(10)
                ->filter('fieldName', 'fieldValue')
                ->build()
        );
    }

    public function testSelectWithLimitAndOrderAsc(): void
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` ORDER BY `sql_test_entity`.`name` ASC LIMIT 0,10',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->limit(10)
                ->orderAsc('name')
                ->build()
        );
    }

    public function testSelectWithFilterAndLimitAndOrder(): void
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` WHERE 1 AND `sql_test_entity`.`fieldName`="fieldValue" ORDER BY `sql_test_entity`.`name` ASC LIMIT 0,10',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->filter('fieldName', 'fieldValue')
                ->limit(10)
                ->orderAsc('name')
                ->build()
        );
    }

    public function testSelectWithOneStringParameterFilterReturnException()
    {

        $this->expectException(InvalidParameterException::class);
        (new MysqlSelect(
            Database::engine()->motor(),
            new EntitySimple()
        ))
            ->filter('key')
            ->build();

    }

    public function testSelectWithOneEntityNotLoadedParameterFilterReturnException()
    {

        $this->expectException(InvalidParameterException::class);
        (new MysqlSelect(
            Database::engine()->motor(),
            new EntitySimple()
        ))
            ->filter(new EntitySimple())
            ->build();

    }

    public function testSelectWithNoParametersFilterReturnException()
    {
        $this->expectException(InvalidParameterException::class);
        (new MysqlSelect(
            Database::engine()->motor(),
            new EntitySimple()
        ))
            ->filter()
            ->build();
    }

    public function testSelectWithNotLoadedEntityFilterReturnException()
    {
        $this->expectException(InvalidParameterException::class);
        (new MysqlSelect(
            Database::engine()->motor(),
            new EntitySimple()
        ))
            ->filter(new EntitySimple())
            ->build();
    }

    public function test_join()
    {
        $entityWithOtherTable = new EntityWithOtherTable();
        $entityWithCustomPrimaryKey = new EntityWithCustomPrimaryKey();
        $entityWithCustomPrimaryKeyAndPrefix = new EntityWithCustomPrimaryKeyAndPrefix();

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
                        .'INNER JOIN `table_other_entity` ON `sql_test_entity`.`id`=`table_other_entity`.`sql_test_entity_id`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->join($entityWithOtherTable)
                ->build()
        );

        $this->assertEquals(
            'SELECT `test_table_cpktp`.* FROM `test_table_cpktp` '
                        .'INNER JOIN `table_other_entity` ON `test_table_cpktp`.`uid`=`table_other_entity`.`test_table_cpktp_uid`',
            (new MysqlSelect(
                Database::engine()->motor(),
                $entityWithCustomPrimaryKey
            ))
                ->join($entityWithOtherTable)
                ->build()
        );
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            .'INNER JOIN `table_other_entity` ON `sql_test_entity`.`id`=`table_other_entity`.`customKey`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->join($entityWithOtherTable,'customKey')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            .'INNER JOIN `table_other_entity` ON `table_other_entity`.`customKey`=`sql_test_entity`.`simpleKey`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->join($entityWithOtherTable,'customKey',new EntitySimple(),'simpleKey')
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` '
            .'INNER JOIN `table_other_entity` ON `table_other_entity`.`customKey`=`test_table_cpktp`.`uid`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->join($entityWithOtherTable,'customKey',new EntityWithCustomPrimaryKey())
                ->build()
        );
    }

    public function testSelectWithLeftJoin()
    {
        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` LEFT JOIN `table_other_entity` ON `sql_test_entity`.`id`=`table_other_entity`.`sql_test_entity_id`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->leftJoin( new EntityWithOtherTable() )
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` LEFT JOIN `table_other_entity` ON `sql_test_entity`.`id`=`table_other_entity`.`otherId`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->leftJoin( new EntityWithOtherTable() , 'otherId' )
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` LEFT JOIN `table_other_entity` ON `table_other_entity`.`otherId`=`test_table_cpktp`.`uid`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->leftJoin( new EntityWithOtherTable() , 'otherId', new EntityWithCustomPrimaryKey() )
                ->build()
        );

        $this->assertEquals(
            'SELECT `sql_test_entity`.* FROM `sql_test_entity` LEFT JOIN `table_other_entity` ON `table_other_entity`.`otherId`=`test_table_cpktp`.`strangerId`',
            (new MysqlSelect(
                Database::engine()->motor(),
                new EntitySimple()
            ))
                ->leftJoin( new EntityWithOtherTable() , 'otherId', new EntityWithCustomPrimaryKey() ,'strangerId' )
                ->build()
        );
    }
}
