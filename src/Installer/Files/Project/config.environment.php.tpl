<?php

use Architekt\DB\DBConnexion;

const APPLICATION_MAIN_DATABASE = '{$DATABASE.name}';
const TABLE_PREFIX = '';

{foreach from=$APPLICATIONS_URLS_PRIMARY key=applicationKeyUpper item=url}
const URL_{$applicationKeyUpper} = '{$url}';
{/foreach}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

DBConnexion::add(
    'main',
    '{$DATABASE.engine}',
    '{$DATABASE.host}',
    '{$DATABASE.user}',
    '{$DATABASE.password}',
    APPLICATION_MAIN_DATABASE
);
