<?php

namespace Website\{$APPLICATION_CAMEL};

use Architekt\Controller;
use Architekt\Plugin;
use Architekt\Http\Request;
use Controllers\{$APPLICATION_CAMEL}Controller;

#[Setting('urls', 'homepage', 'Default User Not Logged url', 'text', '/User/login')]
class RedirectController extends {$APPLICATION_CAMEL}Controller
{

{include file='./../../../templates/controllerHeader.tpl' name='Redirect' uri='Redirect'}

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
        if (!$this->__user()) {
            Request::redirect('/');
        }

        if ($this->__applicationUser()) {
            Request::redirect('/{$APPLICATION_USER_CAMEL}/Redirect');
        }

        if ($this->__user()->profile()->allow('User/Index', 'multiple')) {
            Request::redirect('/{$APPLICATION_USER_CAMEL}/choose');
        }

        Request::redirect('/{$APPLICATION_USER_CAMEL}/autocreate');
    }
}