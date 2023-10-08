<?php

namespace Website\{$APPLICATION_CAMEL}\User;

use Architekt\Application;
use Architekt\Controller;
use Architekt\Plugin;
use Architekt\Http\Request;
use Controllers\{$APPLICATION_CAMEL}Controller;

#[Setting('urls', 'homepage', 'Default User Not Logged url', 'text', '/User/login')]
#[Setting('urls', 'user', 'Default User Logged url', 'text', '/Home')]
#[Setting('urls', '{$APPLICATION_USER}', 'Default {$APPLICATION_USER_CAMEL} Logged url', 'text', '/{$APPLICATION_USER_CAMEL}/home')]
class RedirectController extends {$APPLICATION_CAMEL}Controller
{

{include file='./../../../../templates/controllerHeader.tpl' name='User/Redirect' uri='User/Redirect'}

    public function __templateVars(): array
    {
        return [];
    }

    public function index(): void
    {
        if ($this->__user()) {
            Request::redirect('/{$APPLICATION_USER_CAMEL}/Redirect');
        }

        Request::redirect('/User/login');
    }
}