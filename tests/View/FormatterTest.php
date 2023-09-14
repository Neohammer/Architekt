<?php

namespace tests\Architekt\DB;

use Architekt\View\Formatter;
use Iterator;
use PHPUnit\Framework\TestCase;

final class FormatterTest extends TestCase
{
    private static function getSmartyObject(): \Smarty_Internal_Template
    {
        return new \Smarty_Internal_Template('test.html', new \Smarty());
    }

    public static function test_date_willReturnAsExpected(): void
    {
        $formatter = new Formatter();
        $smartyRequire = self::getSmartyObject();

        self::assertSame('25/06/2023', $formatter->date(['date' => '2023-06-25'], $smartyRequire));
        self::assertSame('25/06/2023', $formatter->date(['date' => '2023-06-25 22:52:32'], $smartyRequire));
        self::assertSame('2023', $formatter->date(['format' => 'Y', 'date' => '2023-06-25 22:52:32'], $smartyRequire));
    }

    public static function test_month_willReturnAsExpected(): void
    {
        $formatter = new Formatter();
        $smartyRequire = self::getSmartyObject();

        self::assertSame('Juin 2023', $formatter->month(['month' => '2023-06-25'], $smartyRequire));
        self::assertSame('Juin 2023', $formatter->month(['month' => '2023-06'], $smartyRequire));
    }

    /**
     * @dataProvider provideUserDates
     */
    public static function test_userdate_willReturnAsExpected(string $provided, string $expected): void
    {
        $formatter = new Formatter();
        $smartyRequire = self::getSmartyObject();

        self::assertSame($expected, $formatter->userDate(['date' => $provided], $smartyRequire));
        self::assertSame($expected, $formatter->userDate(['date' => $provided . ' 22:52:32'], $smartyRequire));
    }

    public static function test_hour_willReturnAsExpected(): void
    {
        $formatter = new Formatter();
        $smartyRequire = self::getSmartyObject();

        self::assertSame('12h25', $formatter->hour(['hour' => '12:25:53'], $smartyRequire));
        self::assertSame('16h25', $formatter->hour(['hour' => '16:25'], $smartyRequire));
        self::assertSame('16h28', $formatter->hour(['hour' => '2023-06-05 16:28:00'], $smartyRequire));
    }

    public static function test_price_willReturnAsExpected(): void
    {
        $formatter = new Formatter();
        $smartyRequire = self::getSmartyObject();

        self::assertSame('25,00&euro;', $formatter->price(['price' => '25'], $smartyRequire));
        self::assertSame('12,30&euro;', $formatter->price(['price' => '12.3'], $smartyRequire));
        self::assertSame('28,39&euro;', $formatter->price(['price' => '28.39'], $smartyRequire));
    }

    public static function provideUserDates(): Iterator
    {
        yield ['2023-01-02', 'Lundi 2 Janvier'];
        yield ['2023-02-07', 'Mardi 7 Février'];
        yield ['2023-03-08', 'Mercredi 8 Mars'];
        yield ['2023-04-06', 'Jeudi 6 Avril'];
        yield ['2023-05-05', 'Vendredi 5 Mai'];
        yield ['2023-06-24', 'Samedi 24 Juin'];
        yield ['2023-07-02', 'Dimanche 2 Juillet'];
        yield ['2023-08-01', 'Mardi 1er Août'];
        yield ['2023-09-01', 'Vendredi 1er Septembre'];
        yield ['2023-10-01', 'Dimanche 1er Octobre'];
        yield ['2023-11-01', 'Mercredi 1er Novembre'];
        yield ['2023-12-01', 'Vendredi 1er Décembre'];
    }
}
