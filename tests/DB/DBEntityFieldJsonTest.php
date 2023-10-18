<?php

namespace tests\Architekt\DB;

use Architekt\DB\DBEntityFieldJson;
use PHPUnit\Framework\TestCase;
use tests\Architekt\DB\EntitySamples\DBEntitySimple;

class DBEntityFieldJsonTest extends TestCase
{
    public function test(): void
    {
        $entity = new DBEntitySimple();


        $json = new DBEntityFieldJson($entity, 'toto');
        self::assertEquals('[]', $json->toString());

        $json->set('field1', 'anothervalue');

        self::assertEquals('{"field1":"anothervalue"}', $json->toString());


        self::assertInstanceOf(DBEntityFieldJson::class, $entity->_get('field_json'));
        self::assertNotInstanceOf(DBEntityFieldJson::class, $entity->_get('json_field'));


        $entity->_set('field_json', '{"field1":"value1","field2":"value2"}');

        self::assertEquals("value1", $entity->_get('field_json')->get('field1'));

    }
}