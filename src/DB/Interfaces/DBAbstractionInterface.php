<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBConnexion;

interface DBAbstractionInterface extends DBRequesterInterface
{
    public function __construct(DBConnexion $DBConnexion);

    public function close(): bool;

}