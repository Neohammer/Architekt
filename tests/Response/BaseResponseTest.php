<?php

namespace tests\Architekt\Response;

use PHPUnit\Framework\TestCase;
use tests\Architekt\Response\ResponseSamples\ResponseSample;

final class BaseResponseTest extends TestCase
{
    public static function test_route_willReturnAsExpected(): void
    {
        $response = new ResponseSample();
        $response->test_init();
        self::assertSame([], $response->test_buildRoute());

        $response->setRedirect('/redirectUrl/to');

        self::assertSame([
            'returnTo' => '/redirectUrl/to'
        ], $response->test_buildRoute());


        $response->setReload('/reloadUrl/to');

        self::assertSame([
            'reloadTo' => '/reloadUrl/to',
        ], $response->test_buildRoute());


        $response->setRedirect('/redirectUrl/to', 'container');

        self::assertSame([
            'returnTo' => '/redirectUrl/to',
            'returnTarget' => 'container',
        ], $response->test_buildRoute());


        $response->setReload('/reloadUrl2/to');

        self::assertSame([
            'reloadTo' => '/reloadUrl2/to',
        ], $response->test_buildRoute());

        $response = new ResponseSample();
        $response->test_init(['testArg' => 1]);
        self::assertSame([], $response->test_buildRoute());

        self::assertSame(1, $response->getArg('testArg'));
        self::assertNull($response->getArg('testFakeArg'));
    }
}
