<?php

namespace tests\Architekt\DB\Motors;

use Architekt\DB\Database;
use Architekt\DB\Entity;
use Architekt\DB\Exceptions\InvalidParameterException;
use Architekt\DB\Motors\Mysql\MysqlDelete;
use Architekt\DB\Motors\Mysql\MysqlInsert;
use Architekt\DB\Motors\Mysql\MysqlSelect;
use Architekt\DB\Motors\Mysql\MysqlUpsert;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\EntitySimple;
use tests\Architekt\DB\EntitySamples\EntityWithOtherTable;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKey;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKeyAndPrefix;

class MysqlUpsertTest extends TestCase
{
    public function testInsert()
    {
        $this->assertSame(
            'INSERT INTO `sql_test_entity` VALUES()',
            (new MysqlUpsert(
                new EntitySimple()
            ))->build()
        );

        $this->assertSame(
            'INSERT INTO `sql_test_entity` (`name`) VALUES ("testName")',
            (new MysqlUpsert(
                (new EntitySimple())->_set('name', 'testName')
            ))->build()
        );

        $this->assertSame(
            'INSERT INTO `sql_test_entity` (`active`, `name`) VALUES ("0", "toto")',
            (new MysqlUpsert(
                (new EntitySimple())->_set(['active' => 0,'name'=>'toto'])
            ))->build()
        );

        $this->assertSame(
            'INSERT INTO `sql_test_entity` (`active`, `name`) VALUES ("0", "to\"to")',
            (new MysqlUpsert(
                (new EntitySimple())->_set(['active' => 0,'name'=>'to"to'])
            ))->build()
        );

        $this->assertSame(
            'INSERT INTO `sql_test_entity` (`active`, `name`) VALUES ("0", null)',
            (new MysqlUpsert(
                (new EntitySimple())->_set(['active' => 0,'name'=>null])
            ))->build()
        );

        $entityOther = EntityWithOtherTable::_test_createSample();
        $entity = (new EntitySimple())->_set(['active' => 0,'name'=>null,$entityOther]);


        $this->assertSame(
            sprintf(
                'INSERT INTO `sql_test_entity` (`active`, `name`, `table_other_entity_id`) VALUES ("0", null, "%s")',
                $entityOther->_primary()
            ),
            (new MysqlUpsert(
                $entity
            ))->build()
        );
    }
    public function testUpdate()
    {
        $entity = EntitySimple::_test_createSample();

        $this->assertSame(
            sprintf(
                'UPDATE `sql_test_entity` SET `name`="test1", `active`="1" WHERE `id`="%d"',
                $entity->_primary()
            ),
            (new MysqlUpsert(
                $entity
            ))->build()
        );

        $entity = EntitySimple::_test_createSample();
        $entity->_set('name');

        $this->assertSame(
            sprintf(
                'UPDATE `sql_test_entity` SET `name`=null, `active`="1" WHERE `id`="%d"',
                $entity->_primary()
            ),
            (new MysqlUpsert(
                $entity
            ))->build()
        );
    }
}
