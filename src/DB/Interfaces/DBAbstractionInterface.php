<?php

namespace Architekt\DB\Interfaces;

use Architekt\DB\DBConnexion;

interface DBAbstractionInterface extends DBConnexionInterface
{
    public function __construct(DBConnexion $DBConnexion);

}