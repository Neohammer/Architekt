<?php

namespace tests\Architekt\DB;

use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\EntityCache;
use tests\Architekt\DB\EntitySamples\EntitySimple;
use tests\Architekt\DB\EntitySamples\EntityWithOtherTable;

class EntityCacheTest extends TestCase
{
    protected function setUp(): void
    {
        EntityWithOtherTable::_test_createTable();
        EntitySimple::_test_createTable();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        EntityWithOtherTable::_test_dropTable();
        EntitySimple::_test_dropTable();
        parent::tearDown();
    }

    public function testFromCacheReturnEntitiesFromCache(): void
    {
        $entityId1 = EntityCache::_test_createRow('test1', '1');
        $entityId2 = EntityCache::_test_createRow('test2', '0');

        $this->assertSame(
            EntityCache::fromCache($entityId1),
            EntityCache::fromCache($entityId1)
        );

        $this->assertNotSame(
            EntityCache::fromCache($entityId2),
            EntityCache::fromCache($entityId1)
        );

        $this->assertTrue(
            EntityCache::fromCache($entityId1)
                ->_isEqualTo(new EntityCache($entityId1))
        );

    }
}
