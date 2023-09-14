<?php

namespace tests\Architekt\DB\Motors;

use Architekt\DB\Database;
use Architekt\DB\Exceptions\InvalidParameterException;
use Architekt\DB\Motors\Mysql\MysqlDelete;
use Architekt\DB\Motors\Mysql\MysqlSelect;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\EntitySimple;
use tests\Architekt\DB\EntitySamples\EntityWithOtherTable;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKey;
use tests\Architekt\DB\EntitySamples\EntityWithCustomPrimaryKeyAndPrefix;

class MysqlDeleteTest extends TestCase
{
    public function testDelete()
    {
        $entity = EntitySimple::_test_createSample();
        $entityId = $entity->_primary();

        $this->assertSame(
            sprintf(
                'DELETE FROM `sql_test_entity` WHERE `id`=%d LIMIT 1',
                $entityId
            ),
            (new MysqlDelete(
                $entity
            ))->build()
        );

    }
}
