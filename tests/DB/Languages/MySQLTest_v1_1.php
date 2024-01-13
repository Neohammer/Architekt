<?php

namespace tests\Architekt\DB\Languages;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBDatabase;
use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use Architekt\DB\DBRecordColumn;
use Architekt\DB\DBRecordRow;
use Architekt\DB\Languages\MySQL;
use PHPUnit\Framework\TestCase;

class MySQLTest_v1_1 extends TestCase
{
    public function test_recordSearchFilterBrackets(): void
    {
        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field AND ( `field1`=:field1 OR `field2`=:field2 )',
                [
                    ':field' => 20,
                    ':field1' => 40,
                    ':field2' => 50,
                ]
            ),
            (new MySQL)->recordSearch()->datatable(new DBDatatable('testTable'), null, true)
                ->filter((new DBRecordRow('testTable'))->and('field', 20))
                ->filterAnd()
                ->filter((new DBRecordRow('testTable'))->and('field1', 40))
                ->filter((new DBRecordRow('testTable'))->or('field2', 50))
                ->filterEnd()
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field OR ( `field1`=:field1 AND `field2`=:field2 )',
                [
                    ':field' => 20,
                    ':field1' => 40,
                    ':field2' => 50,
                ]
            ),
            (new MySQL)->recordSearch()->datatable(new DBDatatable('testTable'), null, true)
                ->filter((new DBRecordRow('testTable'))->and('field', 20))
                ->filterOr()
                    ->filter((new DBRecordRow('testTable'))->and('field1', 40))
                    ->filter((new DBRecordRow('testTable'))->and('field2', 50))
                ->filterEnd()
                ->query()
        );
    }

}