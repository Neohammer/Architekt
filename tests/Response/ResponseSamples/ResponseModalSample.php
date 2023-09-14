<?php

namespace tests\Architekt\Response\ResponseSamples;

use Architekt\Response\Modal;

class ResponseModalSample extends Modal
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
