<?php

namespace Website\{$APPLICATION_CAMEL};

use Controllers\{$APPLICATION_CAMEL}Controller;
use Architekt\Plugin;

#[Access('none', 'All', 'Allow home display')]
class HomeController extends {$APPLICATION_CAMEL}Controller
{
    public function __plugin(): Plugin
    {
        return Plugin::fromCache(6);
    }

    #[Access('prout')]
    public function index(): void
    {
        $this->initView('emptypage')->render();
    }
}