<?php

namespace Website\{$APPLICATION_CAMEL}\Architekt;

use Architekt\Controller;
use Architekt\Plugin;
use Controllers\{$APPLICATION_CAMEL}Controller as ApplicationInternalController;

class ApplicationController extends ApplicationInternalController
{

{include file = './../../../../templates/controllerHeader.tpl' name='Architekt/Application'  uri='Architekt/Application'}

    public function list(): void
    {
        $search = new Application();
        $search->_search()->orderAsc($search, 'name');

        $this
            ->initView()
            ->setHtmlTitle('Liste des applications')
            ->assign('RESULTS' , $search->_results())
            ->render();
    }
}