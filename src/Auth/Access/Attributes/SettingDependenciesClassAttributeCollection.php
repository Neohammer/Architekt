<?php

namespace Architekt\Auth\Access\Attributes;

class SettingDependenciesClassAttributeCollection
{
    /** @var SettingDependencyClassAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(SettingDependencyClassAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /**
     * @return static
     */
    static public function parse(array $classAttributes): static
    {
        $that = new self();
        if (isset($classAttributes['SettingDependency'])) {
            foreach ($classAttributes['SettingDependency'] as $settings) {
                $that->add(new SettingDependencyClassAttribute(...$settings));
            }
        }

        return $that;
    }
}