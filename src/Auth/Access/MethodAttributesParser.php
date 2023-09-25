<?php

namespace Architekt\Auth\Access;

use Architekt\Auth\Access\Attributes\AccessAttributeCollection;
use Architekt\Auth\Access\Attributes\DependencyAttributeCollection;
use Architekt\Auth\Access\Attributes\DescriptionAttribute;
use Architekt\Auth\Access\Attributes\LoggedAttribute;
use Architekt\Auth\Access\Attributes\LoggedUserAttribute;
use Architekt\Auth\Access\Attributes\SettingAttributeCollection;
use Architekt\Auth\Access\Attributes\SettingDependenciesAttributeCollection;

class MethodAttributesParser
{
    private ClassAttributesParser $classAttributesParser;
    private string $method;

    public function __construct(
        ClassAttributesParser $classAttributesParser,
        string $method
    )
    {
        $this->classAttributesParser = $classAttributesParser;
        $this->method = $method;
    }

    public function description(): string
    {
        return (DescriptionAttribute::parse($this->classAttributesParser->methodAttributes($this->method)))->description;
    }

    public function accesses(): AccessAttributeCollection
    {
        return AccessAttributeCollection::parse($this->classAttributesParser->methodAttributes($this->method));
    }

    public function dependencies(): DependencyAttributeCollection
    {
        return DependencyAttributeCollection::parse($this->classAttributesParser->methodAttributes($this->method));
    }

    public function settingDependencies(): SettingDependenciesAttributeCollection
    {
        return SettingDependenciesAttributeCollection::parse($this->classAttributesParser->methodAttributes($this->method));
    }

    public function settings(): SettingAttributeCollection
    {
        return SettingAttributeCollection::parse($this->classAttributesParser->methodAttributes($this->method));
    }

    public function logged(): LoggedAttribute
    {
        return LoggedAttribute::parse($this->classAttributesParser->methodAttributes($this->method),$this->classAttributesParser->logged());
    }

    public function loggedUser(): LoggedUserAttribute
    {
        return LoggedUserAttribute::parse($this->classAttributesParser->methodAttributes($this->method));
    }
}