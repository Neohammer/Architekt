<?php

namespace Architekt\Auth\Access\Attributes;

class SettingDependenciesAttributeCollection
{
    /** @var SettingDependencyAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(SettingDependencyAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /**
     * @return SettingDependencyAttribute[]
     */
    public function get(): array
    {
        return $this->attributes;
    }

    /**
     * @return static
     */
    static public function parse(array $methodAttributes): static
    {
        $that = new self();
        if (isset($methodAttributes['SettingDependency'])) {
            foreach ($methodAttributes['SettingDependency'] as $settings) {
                $that->add(new SettingDependencyAttribute(...$settings));
            }
        }

        return $that;
    }
}