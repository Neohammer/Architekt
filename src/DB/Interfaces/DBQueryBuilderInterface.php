<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\Abstraction\Query;

interface DBQueryBuilderInterface
{
    public function query(): Query;
}