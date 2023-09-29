<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBDatabase;

interface DBDatabaseSearchInterface
{
    public function filter(DBDatabase $database): static;

}