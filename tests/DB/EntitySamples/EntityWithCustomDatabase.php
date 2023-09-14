<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Entity;

class EntityWithCustomDatabase extends Entity
{
    protected static ?string $_table = 'sql_test_entity';
    protected static ?string $_database = 'customdatabase';
}
