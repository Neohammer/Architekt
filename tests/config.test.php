<?php


use Architekt\DB\DBConnexion;

const APPLICATION_MAIN_DATABASE = 'tests';
const TABLE_PREFIX = '';

$_SERVER['SERVER_NAME'] = 'test.mon-domaine.fr';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

DBConnexion::add(
    'main',
    DBConnexion::MYSQL,
    'localhost',
    'root',
    '',
    APPLICATION_MAIN_DATABASE
);