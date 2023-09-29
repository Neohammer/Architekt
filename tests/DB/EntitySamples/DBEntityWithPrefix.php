<?php

namespace tests\Architekt\DB\EntitySamples;

class DBEntityWithPrefix extends DBEntitySimple
{
    protected static ?string $_table_prefix = 'prefixed_';
}
