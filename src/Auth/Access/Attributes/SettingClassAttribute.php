<?php

namespace Architekt\Auth\Access\Attributes;

class SettingClassAttribute
{
    public mixed $values;
    public mixed $default;

    public function __construct(
        public bool $customizable,
        public string $code,
        public string $subCode,
        public string $description,
        public string $type,
        mixed  $valuesOrDefaultForBooleanAndCustomText,
        mixed  $default = null,
    )
    {
        if($type === 'bool' || $type ==='text' ){
            $this->default = $valuesOrDefaultForBooleanAndCustomText;
            $this->values = null;
        }
        else{
            $this->default = $default;
            $this->values = $valuesOrDefaultForBooleanAndCustomText;
        }
    }

    public function isCheckbox(): bool
    {
        return $this->type === 'bool';
    }

    public function isCustomText(): bool
    {
        return $this->type === 'text';
    }

    public function profileCanChange(): bool
    {
        return $this->customizable;
    }
}