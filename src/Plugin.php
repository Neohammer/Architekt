<?php

namespace Architekt;

use Architekt\Auth\Access\ClassAttributesParser;
use Architekt\Auth\Access\ControllerParser;
use Architekt\DB\Entity;
use Architekt\DB\EntityCache;

class Plugin extends Entity
{
    use EntityCache;

    protected static ?string $_table = 'plugin';

    public function settings(): ClassAttributesParser
    {
        return ControllerParser::attributes($this);
    }

    public function labelOption(): string
    {
        return sprintf(
            '%s > %s',
            $this->_get('app'),
            $this->_get('name')
        );
    }
}