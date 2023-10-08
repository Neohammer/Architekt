<?php

namespace Website\{$APPLICATION_CAMEL}\Index;

use Architekt\Controller;
use Architekt\Plugin;
use Controllers\{$APPLICATION_CAMEL}Controller;

class IndexController extends {$APPLICATION_CAMEL}Controller
{

{include file='./../../../../templates/controllerHeader.tpl' name='Index/Index' uri=''}

    public function index(): void
    {
        $this->initView('fullpage')->render();
    }

    #[Logged]
    public function home(): void
    {
        $this->initView()->render();
    }
}