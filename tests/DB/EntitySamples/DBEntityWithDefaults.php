<?php

namespace tests\Architekt\DB\EntitySamples;

class DBEntityWithDefaults extends DBEntitySimple
{
    protected function _setDefaults(): void
    {
        $this->_set([
            'name' => 'WriteNameHere'
        ]);
    }
}
