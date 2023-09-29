<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\DBEntity;

class DBEntityWithCustomDatabase extends DBEntity
{
    protected static ?string $_table = 'sql_test_entity';
    protected static ?string $_database = 'customdatabase';
}
