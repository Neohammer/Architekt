<?php

namespace tests\Architekt\DB\EntitySamples;

use Architekt\DB\Entity;

class EntityWithCustomPrimaryKeyAndPrefix extends Entity
{
    protected static string $_primaryKey = 'cpktpid';
    protected static ?string $_table = 'test_table_cpktp';
    protected static ?string $_table_prefix = 'cusp_';
}
