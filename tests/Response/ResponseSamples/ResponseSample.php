<?php

namespace tests\Architekt\Response\ResponseSamples;

use Architekt\Response\BaseResponse;

class ResponseSample extends BaseResponse
{
    public function test_init(?array $args = null): void
    {
        $this->init($args);
    }

    public function test_buildRoute(): array
    {
        return $this->buildRoute();
    }
    /**
     * @return void
     */
    public function send(): void
    {
    }
}
