<?php

namespace tests\Architekt\DB;

use Architekt\View\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    public static function test_message_willReturnAsExpected(): void
    {
        Message::addSuccess('Success message');

        self::assertSame([
            'type' => 'success',
            'text' => 'Success message',
        ], Message::get());

        self::assertNull(Message::get());

        Message::addSuccess('Success message');
        Message::addError('Error message');

        self::assertSame([
            'type' => 'danger',
            'text' => 'Error message',
        ], Message::get());

        self::assertNull(Message::get());
    }
}
