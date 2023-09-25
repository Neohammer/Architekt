<?php

namespace Architekt\Auth\Access\Attributes;

class DependencyAttributeCollection
{
    /** @var DependencyAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(DependencyAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
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
                $that->add(new DependencyAttribute($item));
            }
        }

        return $that;
    }
}