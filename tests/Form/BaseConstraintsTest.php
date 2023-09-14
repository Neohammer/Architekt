<?php

namespace tests\Architekt\Form;

use Architekt\Form\BaseConstraints;
use PHPUnit\Framework\TestCase;

final class BaseConstraintsTest extends TestCase
{
    public function test__autoCheckString(): void
    {
        $this->assertNull(BaseConstraints::_autoCheckString(''));
        $this->assertNull(BaseConstraints::_autoCheckString(null));
        $this->assertNull(BaseConstraints::_autoCheckString('test1', '[a-z]+'));
        $this->assertEquals('test1', BaseConstraints::_autoCheckString('<span>test1</span>'));
        $this->assertEquals('test1', BaseConstraints::_autoCheckString(' test1 '));
    }

    public function test_validateDate(): void
    {
        $this->assertEquals(true, BaseConstraints::validateDate('2022-01-03'));
        $this->assertEquals(true, BaseConstraints::validateDate('03/01/2022'));
        $this->assertEquals(false, BaseConstraints::validateDate('/01/2022'));

    }
}
