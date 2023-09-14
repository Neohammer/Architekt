<?php

namespace tests\Architekt\DB\EntitySamples;

class EntityWithCustomLabel extends EntitySimple
{
    public function label(): string
    {
        return sprintf(
            '%s is %s',
            $this->_get('name'),
            $this->_get('active') ? 'active' : 'unactive'
        );
    }
}
