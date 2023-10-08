<?php

namespace Architekt\Auth\Access\Attributes;

class DependencyClassAttributeCollection
{
    /** @var DependencyClassAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(DependencyClassAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /** @return DependencyClassAttribute[] */
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
        if (isset($classAttributes['Dependency'])) {
            $list = array_keys(array_column($classAttributes['Dependency'], null, 0));
            foreach ($list as $item) {
                $that->add(new DependencyClassAttribute($item));
            }
        }

        return $that;
    }
}