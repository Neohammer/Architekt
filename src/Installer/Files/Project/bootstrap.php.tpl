<?php

use Architekt\Http\Environment;
use Architekt\Http\Request;
use Architekt\Logger;
use Architekt\Transaction;

require(__DIR__ . '/constants.php');
require(__DIR__ . '/classes/_autoloader.php');
require(dirname(__DIR__,1) . '/vendor/autoload.php');

{foreach from=$APPLICATIONS_DOMAINS_BY_ENVIRONMENT key=environmentKey item=domains}
Environment::add('{$environmentKey}', [
{foreach from=$domains item=domain}
    '{$domain}',
{/foreach}
]);
{/foreach}

Environment::requireFile(__DIR__ . '/environments/config');

Logger::setPath(PATH_FILER.'/Logs');

function architekt_error_handler(
    int    $errno,
    string $errstr,
    string $errfile,
    int    $errline
)
{
    static $launched = false;
    if (false === $launched) {
        if (Logger::addPhpError($errno, $errstr, $errfile, $errline)) {
            $launched = true;
            Transaction::rollback();
            Request::to500();
        }
    }
}


//set_error_handler("architekt_error_handler", E_ALL | E_STRICT);