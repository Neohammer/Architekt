<?php

namespace Architekt\Auth\Access\Attributes;

class SettingAttributeCollection
{
    /** @var SettingAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(SettingAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /**
     * @return static
     */
    static public function parse(array $methodAttributes): static
    {
        $that = new self();
        if (isset($methodAttributes['Setting'])) {
            foreach ($methodAttributes['Setting'] as $settings) {
                $that->add(new SettingAttribute(...$settings));
            }
        }

        return $that;
    }
}