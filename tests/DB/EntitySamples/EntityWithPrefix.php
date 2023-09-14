<?php

namespace tests\Architekt\DB\EntitySamples;

class EntityWithPrefix extends EntitySimple
{
    protected static ?string $_table_prefix = 'prefixed_';
}
