<?php

namespace tests\Architekt\DB;

use Architekt\Http\Exceptions\InvalidServerConfigurationException;
use Architekt\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function test_route_willReturnAsExpected(): void
    {
        self::assertSame('routeTest/', Request::route('routeTest/'));
        $_GET['q'] = 'primary:1,except:false';
        self::assertSame('routeTest?q=primary:1,except:false', Request::route('routeTest'));
    }

    public function test_get_willReturnAsExpected(): void
    {
        $_GET = [
            'toto' => 'test'
        ];

        self::assertSame($_GET, Request::getAll());
        self::assertSame('test', Request::get('toto'));
        self::assertSame('default', Request::get('unexisting', 'default'));
        self::assertSame('GET', Request::method());
    }

    public function test_file_willReturnAsExpected(): void
    {
        $_FILES = [
            'file1' => ['name' => 'test', 'tmmname' => 'wdkslmfd.tmp']
        ];

        self::assertSame(['name' => 'test', 'tmmname' => 'wdkslmfd.tmp'], Request::file('file1'));
        self::assertNull(Request::file('file2'));

        $_FILES = null;
    }

    public function test_xhrRequest_willReturnAsExpected(): void
    {
        self::assertFalse(Request::isXhrRequest());
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'test';
        self::assertFalse(Request::isXhrRequest());
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        self::assertTrue(Request::isXhrRequest());

        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function test_modalRequest_willReturnAsExpected(): void
    {
        self::assertFalse(Request::isModalRequest());

        $_GET['returnType'] = 'modal';
        self::assertTrue(Request::isModalRequest());
        unset($_GET['returnType']);

        $_POST['returnType'] = 'modal';
        self::assertTrue(Request::isModalRequest());
        unset($_POST);
    }

    public function test_post_willReturnAsExpected(): void
    {
        $_POST = [
            'toto' => 'test',
            'testArray' => [
                1 => 'row1',
                2 => 'row2'
            ]
        ];
        self::assertSame($_POST, Request::postAll());
        self::assertSame('test', Request::post('toto'));
        self::assertNull(Request::post('unexisting'));
        self::assertSame('default', Request::post('unexisting', 'default'));
        self::assertSame([
            1 => 'row1',
            2 => 'row2'
        ], Request::postArray('testArray'));
        self::assertNull(Request::postArray('toto'));
        self::assertEquals([], Request::postArray('toto', []));

        unset($_POST);
    }

    public function test_session_willReturnAsExpected(): void
    {
        $_SESSION = [
            'toto' => 'test' ,
            'testArray' => [
                1 => 'row1',
                2 => 'row2'
            ],
        ];

        self::assertSame($_SESSION, Request::sessionAll());
        self::assertSame('test', Request::session('toto'));
        self::assertSame('default', Request::session('unexisting', 'default'));
        self::assertSame(false, Request::session('unexisting', false));
        self::assertSame([
            1 => 'row1',
            2 => 'row2'
        ], Request::sessionArray('testArray'));
        self::assertNull(Request::sessionArray('toto'));


        Request::sessionSet('testKey', 'testValue');
        self::assertSame('testValue', Request::session('testKey'));
        self::assertSame('testValue', Request::sessionFlush('testKey'));
        self::assertNull(Request::session('testKey'));

        $_SESSION = [];
    }

    public function test_session_whenSessionNotStartedWillReturnInvalidServerConfigurationException(): void
    {
        unset($_SESSION);
        self::expectException(InvalidServerConfigurationException::class);
        Request::sessionAll();
    }

    public function test_filters_willReturnAsExpected(): void
    {
        self::assertFalse(Request::hasFilters());
        self::assertNull(Request::getFilters());

        $_GET['q'] = 'primary:1,except:false';

        self::assertTrue(Request::hasFilters());
        self::assertSame(
            [
                'primary' => '1',
                'except' => 'false',
            ],
            Request::getFilters()
        );

        $_GET['q'] = 'primary:1:except:false';

        self::assertSame(
            [
            'primary' => '1',
        ],
            Request::getFilters()
        );

        $_GET['q'] = 'primary,1,except,false';

        self::assertSame(
            [
            'primary' => null,
            '1' => null,
            'except' => null,
            'false' => null,
        ],
            Request::getFilters()
        );

        unset($_GET);
    }


}
