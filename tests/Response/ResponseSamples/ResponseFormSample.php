<?php

namespace tests\Architekt\Response\ResponseSamples;

use Architekt\Response\Form;

class ResponseFormSample extends Form
{
    public function test_init(?array $args = null): void
    {
        $this->init($args);
    }

    public function test_buildRoute(): array
    {
        return $this->buildRoute();
    }
}
