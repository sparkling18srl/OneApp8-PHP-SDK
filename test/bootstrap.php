<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!ini_get('date.timezone')) {
    date_default_timezone_get('GMT');
}

// include the composer autoloader
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->add('OneApp8\\Test', __DIR__);
if (!defined("ONE_APP_8_CONFIG_PATH")) {
    define("ONE_APP_8_CONFIG_PATH", __DIR__);
}
