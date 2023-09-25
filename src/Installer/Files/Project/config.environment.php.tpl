<?php

use Architekt\DB\Database;

const APPLICATION_MAIN_DATABASE = 'exile_v1';
const TABLE_PREFIX = '';

{foreach from=$APPLICATIONS_URLS_PRIMARY key=applicationKeyUpper item=url}
const URL_{$applicationKeyUpper} = '{$url}';
{/foreach}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


Database::configure(
    'main',
    Database::MYSQLI,
    'localhost',
    'root',
    '',
    APPLICATION_MAIN_DATABASE
);
