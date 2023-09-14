<?php

namespace tests\Architekt\DB\EntitySamples;

class EntityWithDefaults extends EntitySimple
{
    protected function _setDefaults(): void
    {
        $this->_set([
            'name' => 'WriteNameHere'
        ]);
    }
}
