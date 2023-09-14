<?php

namespace Architekt\Response;

class Modal extends BaseResponse
{

    public string $title;
    public string $content;
    public ?string $actionName;
    public string $type;
    public string $method;
    public string $route;
    public ?string $size;
    public string $className;

    public function __construct(
        string  $title,
        string  $content,
        string  $type,
        string  $method,
        string  $route,
        ?string $size = null,
        string  $actionName = null,
        string $className = 'info'
    )
    {
        $this->title = $title;
        $this->content = $content;
        $this->actionName = $actionName;
        $this->type = $type;
        $this->method = $method;
        $this->route = $route;
        $this->size = $size;
        $this->className = $className;
    }


    public function send(): void
    {
        echo json_encode($this->buildRoute());
        exit();
    }

    protected function buildRoute(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'action' => $this->actionName,
            'type' => $this->type,
            'method' => $this->method,
            'route' => $this->route,
            'size' => $this->size ?? '',
            'isForm' => true,
            'className' => $this->className,
        ];
    }
}