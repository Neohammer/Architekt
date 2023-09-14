<?php

namespace tests\Architekt\Response;

use PHPUnit\Framework\TestCase;
use tests\Architekt\Response\ResponseSamples\ResponseModalSample;

final class ModalTest extends TestCase
{
    public function test_construct_willReturnAsExpected(): void
    {
        $response = new ResponseModalSample(
            'Modal title',
            '<p>My html in Modal</p>',
            'type',
            'method',
            '/path/to/',
            'xl',
            'Action',
            'success',
        );

        self::assertSame([
            'title' => 'Modal title',
            'content' => '<p>My html in Modal</p>',
            'action' => 'Action',
            'type' => 'type',
            'method' => 'method',
            'route' => '/path/to/',
            'size' => 'xl',
            'isForm' => true,
            'className' => 'success',
        ], $response->test_buildRoute());

    }
}
