<?php

namespace Architekt\View;

use Architekt\Application;
use Architekt\Http\Controller;
use Architekt\Http\Request;
use Smarty\Smarty;

class Template extends Smarty
{
    const EXTENSION = 'html';

    private Controller $controller;

    private array $medias;

    private string $htmlTitle;

    public function __construct()
    {
        $this->medias = [
            'css' => [],
            'js' => [],
            'js_internal' => [],
        ];
        $this->htmlTitle = '';
        parent::__construct();
    }

    public function render(): self
    {
        return $this->display($this->controller->viewFile());
    }

    public function getHtml(): string
    {
        return $this->get($this->controller->viewFile());
    }

    public function setController(Controller $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function setHtmlTitle($htmlTitle): self
    {
        $this->htmlTitle = $htmlTitle;
        return $this;
    }

    public function getHtmlTitleScript(): string
    {
        return $this->htmlTitle ? sprintf('<script>$(document).prop("title","%s")</script>', $this->htmlTitle) : '';
    }

    private function getMediasVars(): array
    {
        $internal = $this->buildInternalJs();
        return [
            'MEDIAS' => [
                'CSS' => $this->medias['css'],
                'JAVASCRIPTS' => [
                    'internal' => $internal,
                    'external' => $this->medias['js'],
                ]
            ]
        ];
    }

    private function buildInternalJs(): string
    {
        if (!$this->controller->isJson) {
            $this->medias['js'] = array_merge($this->medias['js'], $this->medias['js_internal']);
            return '';
        }
        $scripts = [];
        foreach ($this->medias['js_internal'] as $js) {
            $scripts[] = sprintf('<script src="%s"></script>', $js);
        }
        return implode('', $scripts);
    }

    public function addMediaLibrary(string $lib): self
    {
        return $this
            ->addMediaCss($lib)
            ->addMediaJs($lib);
    }

    public function addMediaCss(string $css): self
    {
        $this->medias['css'][] =
        $configurator = Application::$configurator->get('medias').$css;
        return $this;
    }

    public function addMediaJs(string $js): self
    {
        $this->medias['js'][] = preg_match('|^http|',$js)?$js:Application::$configurator->get('medias').$js.'.js';
        return $this;
    }

    public function addMediaJsInternal(string $js): self
    {
        $this->medias['js_internal'][] = Application::$configurator->get('medias').$js;
        return $this;
    }

    public function get(string $template): bool|string
    {
        $this->init();

        return
            (!$this->controller->isJson ? $this->fetch('interface/header.' . self::EXTENSION) : '')
            . $this->fetch($template . '.' . self::EXTENSION)
            . ($this->controller->isJson && !$this->controller->isModal ? $this->getHtmlTitleScript() : '')
            . (!$this->controller->isJson ? $this->fetch('interface/footer.' . self::EXTENSION) : '');
    }

    private function init()
    {
        $this
            ->addTemplateDir($this->controller->baseViewPath())
            ->addTemplateDir($this->controller->viewPath())
            ->setCompileDir(PATH_CACHE . 'Smarty/compile/')
            ->setCacheDir(PATH_CACHE . 'Smarty/cache/')
            ->assign($this->getMediasVars())
            ->assign([
                'QUERY' => Request::getFilters(),
                'TITLE' => $this->htmlTitle,
                'USER' => $this->controller->__user()
            ]);

        $this->registerObject('Formatter', new Formatter());


    }

    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null): self
    {
        echo $this->get($template);

        return $this;
    }

    public function displayHtml($template = null, $cache_id = null, $compile_id = null, $parent = null): self
    {
        $this
            ->addTemplateDir($this->controller->baseViewPath())
            ->addTemplateDir($this->controller->viewPath())
            ->setCompileDir(PATH_FILER . 'Smarty/compile/');

        parent::display($template . '.' . self::EXTENSION, $cache_id, $compile_id, $parent);

        return $this;
    }

    public function assign($tpl_var, $value = null, $nocache = false, $scope = null): self
    {
        parent::assign($tpl_var, $value, $nocache);

        return $this;
    }
}