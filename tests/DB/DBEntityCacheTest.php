<?php

namespace tests\Architekt\DB;

use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\DBEntityCache;
use tests\Architekt\DB\EntitySamples\DBEntitySimple;
use tests\Architekt\DB\EntitySamples\DBEntityWithOtherTable;
use tests\Architekt\DB\EntitySamples\EntityCache;
use tests\Architekt\DB\EntitySamples\EntitySimple;
use tests\Architekt\DB\EntitySamples\EntityWithOtherTable;

class DBEntityCacheTest extends TestCase
{
    protected function setUp(): void
    {
        DBEntityWithOtherTable::_test_createTable();
        DBEntitySimple::_test_createTable();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        DBEntityWithOtherTable::_test_dropTable();
        DBEntitySimple::_test_dropTable();
        parent::tearDown();
    }

    public function testFromCacheReturnEntitiesFromCache(): void
    {
        $entityId1 = DBEntityCache::_test_createRow('test1', '1');
        $entityId2 = DBEntityCache::_test_createRow('test2', '0');

        $this->assertSame(
            DBEntityCache::fromCache($entityId1),
            DBEntityCache::fromCache($entityId1)
        );

        $this->assertNotSame(
            DBEntityCache::fromCache($entityId2),
            DBEntityCache::fromCache($entityId1)
        );

        $this->assertTrue(
            DBEntityCache::fromCache($entityId1)
                ->_isEqualTo(new DBEntityCache($entityId1))
        );

    }
}
