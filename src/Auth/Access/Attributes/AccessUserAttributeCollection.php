<?php

namespace Architekt\Auth\Access\Attributes;

class AccessUserAttributeCollection
{
    /** @var AccessAttribute[] */
    private array $attributes;

    private function __construct()
    {
        $this->attributes = [];
    }

    private function add(AccessAttribute $accessAttribute): void
    {
        $this->attributes[] = $accessAttribute;
    }

    /**
     * @return AccessAttribute[]
     */
    public function get(): array
    {
        return $this->attributes;
    }

    public function has(string $accessCode): bool
    {
        foreach($this->attributes as $accessAttribute){
            if($accessAttribute->code === $accessCode){
                return true;
            }
        }

        return false;
    }

    /**
     * @return static
     */
    static public function parse(array $methodAttributes): static
    {
        $that = new self();
        if (isset($methodAttributes['AccessUser'])) {
            $list = array_keys(array_column($methodAttributes['AccessUser'], null, 0));
            foreach ($list as $item) {
                $that->add(new AccessAttribute($item));
            }
        }

        return $that;
    }
}