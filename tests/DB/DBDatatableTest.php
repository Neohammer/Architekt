<?php

namespace tests\Architekt\DB\Motors;

use Architekt\DB\DBDatatable;
use Architekt\DB\DBDatatableColumn;
use PHPUnit\Framework\TestCase;

class DBDatatableTest extends TestCase
{
    public function test_json(): void
    {
        $datatable = new DBDatatable('testDatatable');
        $datatable
            ->addColumn(DBDatatableColumn::buildAutoincrement())
            ->addColumn(DBDatatableColumn::buildString('name', 50));

        $json = '{"name":"testDatatable","columns":[{"name":"id","type":"numeric","nullable":false,"primary":true,"unsigned":true,"autoincrement":true,"hasDefault":false},{"name":"name","type":"string","length":50,"nullable":false,"primary":false,"multiLines":false,"autoincrement":false,"hasDefault":false}]}';

        $this->assertEquals(
            $json,
            $datatable->toJson()
        );

        $datatableFromJson = DBDatatable::fromJson($json);

        $this->assertEquals(
            $datatable,
            $datatableFromJson
        );
    }
}