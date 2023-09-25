<?php

namespace Architekt\Auth\Access\Attributes;

class SettingClassAttribute
{
    public string $code;
    public string $description;
    public string $type;
    public mixed $values;
    public mixed $default;

    public function __construct(
        string $code,
        string $description,
        string $type,
        mixed  $valuesOrDefaultForBoolean,
        mixed  $default = null,
    )
    {
        $this->code = $code;
        $this->description = $description;
        $this->type = $type;
        if($type === 'bool'){
            $this->default = $valuesOrDefaultForBoolean;
            $this->values = null;
        }
        else{
            $this->default = $default;
            $this->values = $valuesOrDefaultForBoolean;
        }
    }

    public function isCheckbox(): bool
    {
        return $this->type === 'bool';
    }
}