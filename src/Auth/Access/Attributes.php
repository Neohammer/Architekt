<?php

namespace Architekt\Auth\Access;

use ReflectionAttribute;
use Reflector;

class Attributes
{
    const ACCESS_NONE = 'none';

    static public function toArray(array $attributes): array
    {
        $return = [];

        /** @var ReflectionAttribute $attribute */
        foreach ($attributes as $attribute) {
            $return[$attribute->getName()] = $attribute->getArguments();
        }

        return $return;
    }

    static public function fromReflector(Reflector $reflector, string $namespace): array
    {
        $return = [];

        /** @var ReflectionAttribute $attribute */
        foreach ($reflector->getAttributes() as $attribute) {
            $name = str_replace($namespace . '\\', '', $attribute->getName());

            if (!array_key_exists($name, $return)) {
                $return[$name] = [];
            }
            $return[$name][] = $attribute->getArguments();
        }

        return $return;
    }
}