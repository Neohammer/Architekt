<?php

namespace Architekt\DB;

use Architekt\DB\Interfaces\DBRecordSearchInterface;

class DBRecordSearchFetcher
{
    public mixed $statement;
    public DBRecordSearchInterface $recordSearch;

    public function __construct(
        DBRecordSearchInterface $recordSearch,
        mixed                   $statement
    )
    {
        $this->recordSearch = $recordSearch;
        $this->statement = $statement;
    }
}