<?php

namespace Architekt\Auth\Access\Attributes;

class AccessClassAttributeCollection
{
    /** @var AccessClassAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(AccessClassAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /**
     * @return AccessClassAttribute[]
     */
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
        if (isset($classAttributes['Access'])) {
            foreach ($classAttributes['Access'] as $access) {
                $that->add(new AccessClassAttribute(
                    $access[0],
                    $access[1] ?? null,
                    $access[2] ?? null
                ));
            }
        }

        return $that;
    }
}