<?php

namespace Website\{$APPLICATION_CAMEL};

use Architekt\Http\Request;
use Controllers\{$APPLICATION_CAMEL}Controller;
use Architekt\Plugin;

class RedirectController extends {$APPLICATION_CAMEL}Controller
{
    public function __plugin(): Plugin
    {
        return Plugin::fromCache(8);
    }

    public function __templateVars(): array
    {
        return [];
    }

    public function error(string $code, ?string $url = null): void
    {
        $this
            ->initView('emptypage')
            ->assign(['ERROR_CODE' => $code, 'ERROR_URL' => $url])
            ->render();
    }

    public function index(): void
    {
        Request::redirect('/Home');
    }
}