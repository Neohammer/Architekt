<?php

namespace tests\Architekt\Response;

use Architekt\Form\Validation;
use PHPUnit\Framework\TestCase;
use tests\Architekt\Response\ResponseSamples\ResponseFormSampleResponse;

final class FormTest extends TestCase
{
    public function test_send_emptySuccessWillReturnAsExpected(): void
    {
        $response = new ResponseFormSampleResponse(
            new Validation(),
            "Success message",
            "Fail message"
        );

        self::assertSame([
            'success' => true,
            'warning' => false,
            'details' => [],
            'message' => 'Success message',
        ], $response->test_buildRoute());

        self::assertTrue($response->isSuccess());
    }


    public function test_send_successWillReturnAsExpected(): void
    {
        $validation = new Validation();
        $validation->addSuccess('input', 'Field 1 is OK');
        $validation->addSuccess('input2', 'Field 2 is really good');
        $validation->addSuccess('input3[toto][titi]', 'Field 3 is really array');

        $response = new ResponseFormSampleResponse(
            $validation,
            "Success message",
            "Fail message"
        );

        self::assertSame([
            'success' => true,
            'warning' => false,
            'details' => [
                [
                    'fields' => ['input'],
                    'success' => true,
                    'message' => 'Field 1 is OK'
                ],
                [
                    'fields' => ['input2'],
                    'success' => true,
                    'message' => 'Field 2 is really good'
                ],
                [
                    'fields' => ['input3toto-titi'],
                    'success' => true,
                    'message' => 'Field 3 is really array'
                ],
            ],
            'message' => 'Success message',
        ], $response->test_buildRoute());

        self::assertTrue($response->isSuccess());
    }

    public function test_send_failWillReturnAsExpected(): void
    {

        $validation = new Validation();
        $validation->addError('input', 'Field is required');

        $response = new ResponseFormSampleResponse(
            $validation,
            "Success message",
            "Fail message"
        );

        self::assertSame(
            [
            'success' => false,
            'warning' => false,
            'details' => [
                [
                    'fields' => ['input'],
                    'success' => false,
                    'message' => 'Field is required'
                ]
            ],
            'message' => 'Fail message',
        ],
            $response->test_buildRoute()
        );

        self::assertFalse($response->isSuccess());
    }
}
