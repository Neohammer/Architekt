<?php

namespace Architekt\Auth\Access;

use Architekt\Auth\Access\Attributes\AccessAttributeCollection;
use Architekt\Auth\Access\Attributes\AccessClassAttributeCollection;
use Architekt\Auth\Access\Attributes\AccessUserAttributeCollection;
use Architekt\Auth\Access\Attributes\DependencyClassAttributeCollection;
use Architekt\Auth\Access\Attributes\LoggedAttribute;
use Architekt\Auth\Access\Attributes\LoggedClassAttribute;
use Architekt\Auth\Access\Attributes\LoggedUserAttribute;
use Architekt\Auth\Access\Attributes\LoggedUserClassAttribute;
use Architekt\Auth\Access\Attributes\SettingClassAttributeCollection;
use Architekt\Auth\Access\Attributes\SettingDependenciesClassAttributeCollection;

class ClassAttributesParser
{
    private \ReflectionClass $reflectionClass;
    private mixed $class;
    private array $classAttributes;
    private array $methodAttributes;

    public function __construct(mixed $class)
    {
        $this->class = $class;
        $this->reflectionClass = new \ReflectionClass($class);
        $this->classAttributes = $this->buildAttributes();

        foreach (get_class_methods($this->class) as $method) {
            $this->methodAttributes[$method] = $this->buildMethodAttributes($method);
        }
    }

    public function accesses(): AccessClassAttributeCollection
    {
        return AccessClassAttributeCollection::parse($this->classAttributes);
    }

    public function methodsWithAccess(string $accessCode): array
    {
        $return = [];

        foreach($this->methodAttributes as $method=>$attributes){
            if((AccessAttributeCollection::parse($attributes))->has($accessCode)){
                $return[] = $method;
            }
        }

        return $return;
    }

    public function methods(): array
    {
        return array_keys($this->methodAttributes);
    }

    public function method(string $method): MethodAttributesParser
    {
        return new MethodAttributesParser($this , $method);
    }

    public function methodAttributes(string $method): array
    {
        return $this->methodAttributes[$method] ?? [];
    }

    public function dependencies(): DependencyClassAttributeCollection
    {
        return DependencyClassAttributeCollection::parse($this->classAttributes);
    }

    public function settingDependencies(): SettingDependenciesClassAttributeCollection
    {
        return SettingDependenciesClassAttributeCollection::parse($this->classAttributes);
    }

    public function settings(): SettingClassAttributeCollection
    {
        return SettingClassAttributeCollection::parse($this->classAttributes);
    }

    public function logged(): LoggedClassAttribute
    {
        return LoggedClassAttribute::parse($this->classAttributes);
    }

    public function loggedUser(): LoggedUserClassAttribute
    {
        return LoggedUserClassAttribute::parse($this->classAttributes);
    }

    private function buildAttributes(): array
    {
        return Attributes::fromReflector($this->reflectionClass, $this->reflectionClass->getNamespaceName()) ?? [];
    }



    /**
     * @throws \ReflectionException
     */
    private function buildMethodAttributes(string $method): array
    {
        $reflectionMethod = new \ReflectionMethod($this->class, $method);

        if (!$reflectionMethod->isPublic()) {
            return [];
        }

        if ($reflectionMethod->getDeclaringClass()->name !== get_class($this->class)) {
            return [];
        }

        return Attributes::fromReflector($reflectionMethod, $this->reflectionClass->getNamespaceName());
    }
}