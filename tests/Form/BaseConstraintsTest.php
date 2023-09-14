<?php

namespace tests\Architekt\Form;

use Architekt\DB\Database;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Engines\DBEngineInterface;
use Architekt\Form\BaseConstraints;
use PHPUnit\Framework\TestCase;

final class BaseConstraintsTest extends TestCase
{

    public function test_validateDate(): void
    {
        $this->assertEquals(true , BaseConstraints::validateDate('2022-01-03'));
        $this->assertEquals(true , BaseConstraints::validateDate('03/01/2022'));
        $this->assertEquals(false , BaseConstraints::validateDate('/01/2022'));

    }
}
