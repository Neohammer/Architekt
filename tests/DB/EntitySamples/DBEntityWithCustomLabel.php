<?php

namespace tests\Architekt\DB\EntitySamples;

class DBEntityWithCustomLabel extends DBEntitySimple
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
