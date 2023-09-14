<?php

namespace Architekt\Response;

class Filter extends BaseResponse
{
    public function __construct()
    {
        parent::init([]);
    }

    public function send(): void
    {
        echo json_encode(array_merge(
            ['success'=>'Liste filtrée'],
            $this->buildRoute()
        ));
        exit();
    }
}