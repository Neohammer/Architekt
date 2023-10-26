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

class MySQLTest extends TestCase
{
    public function test_databaseSearch(): void
    {
        self::assertEquals(
            new Query(
                'SHOW DATABASES'
            ),
            (new Mysql)
                ->databaseSearch()
                ->query()
        );

        self::assertEquals(
            new Query(
                'SHOW DATABASES LIKE :databaseName',
                [':databaseName' => 'testDatabase']
            ),
            (new Mysql)
                ->databaseSearch()
                ->filter(new DBDatabase('testDatabase'))
                ->query()
        );
    }

    public function test_databaseExists(): void
    {
        self::assertEquals(
            new Query(
                'SHOW DATABASES LIKE :databaseName',
                [':databaseName' => 'testDatabase']
            ),
            (new Mysql)->databaseExists(new DBDatabase('testDatabase'))
        );
    }

    public function test_databaseCreate(): void
    {
        self::assertEquals(
            new Query(
                'CREATE DATABASE IF NOT EXISTS `testDatabase`'
            ),
            (new MySQL)->databaseCreate(new DBDatabase('testDatabase'))
        );
    }

    public function test_databaseDelete(): void
    {
        self::assertEquals(
            new Query(
                'DROP DATABASE IF EXISTS `testDatabase`'
            ),
            (new MySQL)->databaseDelete(new DBDatabase('testDatabase'))
        );
    }

    public function test_datatableCreate(): void
    {
        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` (`id` INT NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildPrimary())
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` (`customPrimaryKey` INT NOT NULL, PRIMARY KEY (`customPrimaryKey`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildPrimary('customPrimaryKey'))
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` (`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` ('
                . '`id` INT NOT NULL AUTO_INCREMENT, '
                . '`name` VARCHAR(50) NOT NULL, '
                . 'PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
                    ->addColumn(DBDatatableColumn::buildString('name', 50))
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` ('
                . '`id` INT NOT NULL AUTO_INCREMENT, '
                . '`name` VARCHAR(50) NOT NULL DEFAULT \'EnterNameHere\', '
                . 'PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
                    ->addColumn(DBDatatableColumn::buildString('name', 50)->setDefault('EnterNameHere'))
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` ('
                . '`id` INT NOT NULL AUTO_INCREMENT, '
                . '`active` TINYINT(1) UNSIGNED NOT NULL, '
                . 'PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
                    ->addColumn(DBDatatableColumn::buildBoolean('active'))
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` ('
                . '`id` INT NOT NULL AUTO_INCREMENT, '
                . '`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0, '
                . 'PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
                    ->addColumn(DBDatatableColumn::buildBoolean('active')->setDefault(false))
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` ('
                . '`id` INT NOT NULL AUTO_INCREMENT, '
                . '`active` TINYINT(1) UNSIGNED NULL DEFAULT NULL, '
                . 'PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
                    ->addColumn(DBDatatableColumn::buildBoolean('active')->setDefault(null))
            )
        );

        $this->assertEquals(
            new Query(
                'CREATE TABLE IF NOT EXISTS `testTable` ('
                . '`id` INT NOT NULL AUTO_INCREMENT, '
                . '`description` TEXT NULL DEFAULT NULL, '
                . 'PRIMARY KEY (`id`)) ENGINE=InnoDb'
            ),
            (new MySQL)->datatableCreate(
                (new DBDatatable('testTable'))
                    ->addColumn(DBDatatableColumn::buildAutoincrement())
                    ->addColumn(DBDatatableColumn::buildString('description', 50, true)->setDefault(null))
            )
        );
    }

    public function test_datatableEmpty(): void
    {
        self::assertEquals(
            new Query(
                'TRUNCATE TABLE `testDatatable`'
            ),
            (new MySQL)->datatableEmpty(new DBDatatable('testDatatable'))
        );
    }

    public function test_recordDelete(): void
    {

        self::assertEquals(
            new Query(
                'DELETE FROM `testDatatable` WHERE `testField`=:testField AND `testField2` IS NULL',
                [':testField' => 'testValue']
            ),
            (new MySQL)->recordDelete(
                (new DBRecordRow('testDatatable'))
                    ->and('testField', 'testValue')
                    ->and('testField2', null)
            )
        );

        self::assertEquals(
            new Query(
                'DELETE FROM `testDatatable` WHERE `testField`>:testField',
                [':testField' => 50]
            ),
            (new MySQL)->recordDelete(
                (new DBRecordRow('testDatatable'))
                    ->andGreater('testField', 50)
            )
        );

        self::assertEquals(
            new Query(
                'DELETE FROM `testDatatable` WHERE `testField`<:testField',
                [':testField' => 50]
            ),
            (new MySQL)->recordDelete(
                (new DBRecordRow('testDatatable'))
                    ->andLower('testField', 50)
            )
        );

        self::assertEquals(
            new Query(
                'DELETE FROM `testDatatable` WHERE `testField`>=:testField',
                [':testField' => 50]
            ),
            (new MySQL)->recordDelete(
                (new DBRecordRow('testDatatable'))
                    ->andGreaterOrEqual('testField', 50)
            )
        );

        self::assertEquals(
            new Query(
                'DELETE FROM `testDatatable` WHERE `testField`<=:testField',
                [':testField' => 50]
            ),
            (new MySQL)->recordDelete(
                (new DBRecordRow('testDatatable'))
                    ->andLowerOrEqual('testField', 50)
            )
        );
    }

    public function test_recordInsert(): void
    {
        self::assertEquals(
            new Query(
                'INSERT INTO `testDatatable` (`testField`, `testField2`) VALUES (:testField, :testField2)',
                [
                    ':testField' => 'testValue',
                    ':testField2' => 'testValue2'
                ]
            ),
            (new MySQL)->recordInsert(
                (new DBRecordRow('testDatatable'))
                    ->set('testField', 'testValue')
                    ->set('testField2', 'testValue2')
            )
        );
        self::assertEquals(
            $query = new Query(
                'INSERT INTO `testDatatable` (`testField`, `testField2`) VALUES (:testField, NULL)',
                [
                    ':testField' => 'testValue'
                ]
            ),
            (new MySQL)->recordInsert(
                (new DBRecordRow('testDatatable'))
                    ->set('testField', 'testValue')
                    ->set('testField2', null)
            )
        );
        self::assertEquals(
            $query,
            (new MySQL)->recordInsert(
                (new DBRecordRow('testDatatable'))
                    ->aset([
                        'testField' => 'testValue',
                        'testField2' => null
                    ])
            )
        );


    }

    public function test_recordUpdate(): void
    {

        self::assertEquals(
            new Query(
                'UPDATE `testDatatable` SET `testField`=:testField WHERE `testFilter`=:testFilter',
                [
                    ':testField' => 'testFieldValue',
                    ':testFilter' => 'testFilterValue',
                ]
            ),
            (new MySQL)->recordUpdate(
                (new DBRecordRow('testDatatable'))
                    ->set('testField', 'testFieldValue')
                    ->and('testFilter', 'testFilterValue')
            )
        );

        self::assertEquals(
            new Query(
                'UPDATE `testDatatable` SET `testField`=:testField, `testField2`=:testField2 WHERE `testFilter`=:testFilter',
                [
                    ':testField' => 'testFieldValue',
                    ':testField2' => 'testFieldValue2',
                    ':testFilter' => 'testFilterValue',
                ]
            ),
            (new MySQL)->recordUpdate(
                (new DBRecordRow('testDatatable'))
                    ->set('testField', 'testFieldValue')
                    ->set('testField2', 'testFieldValue2')
                    ->and('testFilter', 'testFilterValue')
            )
        );

    }


    public function test_recordSearchLeftAndInner(): void
    {
        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` LEFT JOIN `testTable2` ON `testTable`.`field1`=`testTable2`.`field2`'
            ),
            (new MySQL)
                ->recordSearch()
                ->datatable(new DBDatatable('testTable'))
                ->datatable(
                    new DBDatatable('testTable2'),
                    (new DBRecordRow('testTable'))
                        ->and('field1', new DBRecordColumn('testTable2', 'field2'))
                )
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` INNER JOIN `testTable2` ON `testTable`.`field1`=`testTable2`.`field2`'
            ),
            (new MySQL)
                ->recordSearch()
                ->datatable(new DBDatatable('testTable'))
                ->datatable(
                    new DBDatatable('testTable2'),
                    (new DBRecordRow('testTable'))
                        ->and('field1', new DBRecordColumn('testTable2', 'field2')),
                    true
                )
                ->query()
        );
    }


    public function test_recordSearchFilterAnd(): void
    {
        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable`'
            ),
            (new MySQL)
                ->recordSearch()
                ->datatable(new DBDatatable('testTable'), null, true)
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field',
                [':field' => 'value']
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->and('field', 'value'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable`, `testTable2` WHERE `testTable`.`field`=:field AND `testTable2`.`field2`=:field2',
                [
                    ':field' => 'value',
                    ':field2' => 'value2',
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->and('field', 'value'))
                ->filter((new DBRecordRow('testTable2'))->and('field2', 'value2'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable`, `testTable2` WHERE `testTable`.`field`=`testTable2`.`field2`'
            ),
            (new MySQL)
                ->recordSearch()
                ->filter(
                    (new DBRecordRow('testTable'))
                        ->and(
                            'field',
                            new DBRecordColumn('testTable2', 'field2')
                        )
                )
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`>:field',
                [
                    ':field' => 'value'
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andGreater('field', 'value'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`<:field',
                [
                    ':field' => 'value'
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andLower('field', 'value'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`>=:field',
                [
                    ':field' => 'value'
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andGreaterOrEqual('field', 'value'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`<=:field',
                [
                    ':field' => 'value'
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andLowerOrEqual('field', 'value'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` LIKE "%search%"',
                []
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andContains('field', 'search'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` NOT LIKE "%search%"',
                []
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andNotContains('field', 'search'))
                ->query()
        );


        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` LIKE "%search%" OR `field2` LIKE "%search2%"',
                []
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andContains('field', 'search'))
                ->filter((new DBRecordRow('testTable'))->orContains('field2', 'search2'))
                ->query()
        );
    }

    public function test_recordSearchFilterBetween(): void
    {

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` BETWEEN :field0 AND :field1',
                [
                    ':field0' => 20,
                    ':field1' => 40,
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andBetween('field', [20,40]))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` NOT BETWEEN :field0 AND :field1',
                [
                    ':field0' => 20,
                    ':field1' => 40,
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->andNotBetween('field', [20,40]))
                ->query()
        );
    }
    public function test_recordSearchFilterOr(): void
    {
        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field',
                [':field' => 'value']
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->or('field', 'value'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable`, `testTable2` WHERE `testTable`.`field`=:field OR `testTable2`.`field2`=:field2',
                [
                    ':field' => 'value',
                    ':field2' => 'value2',
                ]
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->or('field', 'value'))
                ->filter((new DBRecordRow('testTable2'))->or('field2', 'value2'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable`, `testTable2` WHERE `testTable`.`field`=`testTable2`.`field2`'
            ),
            (new MySQL)
                ->recordSearch()
                ->filter(
                    (new DBRecordRow('testTable'))
                        ->or(
                            'field',
                            new DBRecordColumn('testTable2', 'field2')
                        )
                )
                ->query()
        );


        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` LIKE "%search%"',
                []
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->orContains('field', 'search'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` NOT LIKE "%search%"',
                []
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->orNotContains('field', 'search'))
                ->query()
        );


        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field` LIKE "%search%" OR `field2` LIKE "%search2%"',
                []
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->orContains('field', 'search'))
                ->filter((new DBRecordRow('testTable'))->orContains('field2', 'search2'))
                ->query()
        );
    }

    public function test_recordSearchSelect(): void
    {

        self::assertEquals(
            new Query(
                'SELECT `field1`, `field2` FROM `testTable`'
            ),
            (new MySQL)
                ->recordSearch()
                ->select(new DBRecordColumn('testTable', 'field1'))
                ->select(new DBRecordColumn('testTable', 'field2'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT `testTable`.`field1`, `testTable2`.`field2` FROM `testTable`, `testTable2`'
            ),
            (new MySQL)
                ->recordSearch()
                ->select(new DBRecordColumn('testTable', 'field1'))
                ->select(new DBRecordColumn('testTable2', 'field2'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT `testTable`.`field1` AS "AliasField", `testTable2`.`field1` AS "AliasField2" FROM `testTable`, `testTable2`'
            ),
            (new MySQL)
                ->recordSearch()
                ->select(new DBRecordColumn('testTable', 'field1', 'AliasField'))
                ->select(new DBRecordColumn('testTable2', 'field1', 'AliasField2'))
                ->query()
        );

    }

    public function test_recordSearchLimit(): void
    {
        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field LIMIT 0,1',
                [':field' => 'value']
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->and('field', 'value'))
                ->limit()
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field LIMIT 20,10',
                [':field' => 'value']
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->and('field', 'value'))
                ->limit(10, 3)
                ->query()
        );
    }

    public function test_recordSearchOrder(): void
    {
        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable` WHERE `field`=:field ORDER BY `fieldOrder` ASC LIMIT 0,1',
                [':field' => 'value']
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->and('field', 'value'))
                ->limit()
                ->orderAsc(new DBRecordColumn('testTable', 'fieldOrder'))
                ->query()
        );

        self::assertEquals(
            new Query(
                'SELECT * FROM `testTable`, `testTable2` WHERE `testTable`.`field`=:field ORDER BY `testTable2`.`fieldOrder` ASC LIMIT 0,1',
                [':field' => 'value']
            ),
            (new MySQL)
                ->recordSearch()
                ->filter((new DBRecordRow('testTable'))->and('field', 'value'))
                ->limit()
                ->orderAsc(new DBRecordColumn('testTable2', 'fieldOrder'))
                ->query()
        );

    }
}