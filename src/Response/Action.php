<?php

namespace Architekt\Response;

class Action extends BaseResponse
{
    public function __construct()
    {
        parent::init([]);
    }

    public function send(): void
    {
        echo json_encode($this->buildRoute());
        exit();
    }
}