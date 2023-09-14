<?php


use Architekt\DB\Database;

const APPLICATION_MAIN_DATABASE = 'tests';
const TABLE_PREFIX = '';

$_SERVER['SERVER_NAME'] = 'test.mon-domaine.fr';

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