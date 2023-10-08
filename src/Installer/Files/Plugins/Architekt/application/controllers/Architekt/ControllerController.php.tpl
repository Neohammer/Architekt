<?php

namespace Website\{$APPLICATION_CAMEL}\Architekt;

use Architekt\Controller;
use Architekt\Plugin;
use Controllers\{$APPLICATION_CAMEL}Controller as ApplicationController;

class ControllerController extends ApplicationController
{

{include file = './../../../../templates/controllerHeader.tpl' name = 'Architekt/Controller' uri='Architekt/Controller'}

    public function list(): void
    {
        $search = new Controller();
        $search->_search()->orderAsc($search, 'name');

        $this
            ->initView()
            ->setHtmlTitle('Liste des controllers')
            ->assign('RESULTS' , $search->_results())
            ->render();
    }
}