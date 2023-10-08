<?php

namespace Architekt\Response;

use Architekt\Response\BaseResponse;
use Architekt\Utility\Settings;

class ModalResponse extends BaseResponse
{

    private string $title;
    private string $html;
    private bool $isForm;
    private ?string $action;

    private string $confirmButtonDisplay;
    private string $confirmButtonAction;
    private string $confirmButtonText;
    private string $confirmButtonClass;
    private string $cancelButtonDisplay;
    private string $cancelButtonText;
    private string $cancelButtonClass;

    public function __construct(
        string $title,
        string $html,
    )
    {
        $this->title = $title;
        $this->html = $html;
        $this->isForm = false;
        $this->action = null;
        $this->confirmButtonDisplay = true;
        $this->confirmButtonAction = "#";
        $this->confirmButtonText = "Valider";
        $this->confirmButtonClass = "success";
        $this->cancelButtonDisplay = true;
        $this->cancelButtonText = "Annuler";
        $this->cancelButtonClass = "danger";
    }

    public function form(string $action): static
    {
        $this->isForm = true;
        $this->action = $action;

        return $this;
    }

    public function confirmButton(
        string $action,
        ?string $text = null,
        ?string $class = null
    ): static
    {
        $this->confirmButtonAction = $action;
        if($text){
            $this->confirmButtonText = $text;
        }
        if($class) {
            $this->confirmButtonClass = $class;
        }

        return $this;
    }

    public function submitButton(
        ?string $text,
        ?string $class = null
    ): static
    {
        $this->confirmButtonText = $text;
        if($class) {
            $this->confirmButtonClass = $class;
        }

        return $this;
    }

    public function cancelButton(string $text, ?string $class = null): static
    {
        $this->cancelButtonText = $text;
        if($class) {
            $this->cancelButtonClass = $class;
        }

        return $this;
    }

    public function send(): void
    {
        echo json_encode($this->buildRoute());
        exit();
    }

    protected function buildRoute(): array
    {
        return [
            'system' => Settings::byApplication()->get('modal','system'),
            'content' => [
                'width' => 'xl',
                'title' => $this->title,
                'html' => $this->html,
            ],
            'action' => [
                'isForm' => $this->isForm,
                'url' => $this->isForm ? $this->action : $this->confirmButtonAction,
            ],
            'confirm' => [
                'display' => $this->confirmButtonDisplay,
                'action' => $this->confirmButtonAction,
                'text' => $this->confirmButtonText,
                'class'=> $this->confirmButtonClass,
            ],
            'cancel' => [
                'display' => $this->cancelButtonDisplay,
                'text' => $this->cancelButtonText,
                'class'=> $this->cancelButtonClass,
            ]
            /*
            'action' => $this->actionName,
            'type' => $this->type,
            'method' => $this->method,
            'route' => $this->route,
            'size' => $this->size ?? '',
            'isForm' => true,
            'className' => $this->className,*/
        ];
    }
}