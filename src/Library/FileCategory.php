<?php

namespace Architekt\Library;

use Architekt\DB\Entity;
use Architekt\DB\EntityCache;

class FileCategory extends Entity
{
    use EntityCache;

    public const OTHER = 8;
    public const WORK_ORDER = 9;
    public const WORK_ORDER_ARCHIVED = 13;

    protected static ?string $_table = 'file_category';
}