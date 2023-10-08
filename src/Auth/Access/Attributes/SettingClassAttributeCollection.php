<?php

namespace Architekt\Auth\Access\Attributes;

class SettingClassAttributeCollection
{
    /** @var SettingClassAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(SettingClassAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /** @return SettingClassAttribute[] */
    public function get(): array
    {
        return $this->attributes;
    }

    /**
     * @return static
     */
    static public function parse(array $classAttributes): static
    {
        $that = new self();
        if (isset($classAttributes['Setting'])) {
            foreach ($classAttributes['Setting'] as $settings) {
                $that->add(new SettingClassAttribute(false, ...$settings));
            }
        }
        if (isset($classAttributes['SettingProfile'])) {
            foreach ($classAttributes['SettingProfile'] as $settings) {
                $that->add(new SettingClassAttribute(true, ...$settings));
            }
        }

        return $that;
    }
}