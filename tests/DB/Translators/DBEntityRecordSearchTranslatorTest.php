<?php

namespace tests\Architekt\DB\Translators;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\Translators\DBEntityRecordSearchTranslator;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\DBEntitySimple;
use tests\Architekt\DB\EntitySamples\DBEntityWithOtherTable;

class DBEntityRecordSearchTranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_entityLoaded(): void
    {

        $entity = DBEntitySimple::_test_createSample();


        $dbEntity = new DBEntitySimple();

        $translator = new DBEntityRecordSearchTranslator($dbEntity);

        $translator->and($dbEntity, $entity->_primary());

        $this->assertNotFalse($translator->recordSearchNext($translator->recordSearchFetcher()));
        $this->assertEquals($entity->_get(), $dbEntity->_get());


    }

}