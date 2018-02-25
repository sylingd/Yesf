<?php
use yesf\Yesf;
define('PROJECT_PATH', realpath(__DIR__ . '/..') . '/');
define('APP_PATH', PROJECT_PATH . 'application/');
define('YESF_ROOT', PROJECT_PATH . 'framework/');
define('YESF_UNIT', 1);
define('YESF_TEST', __DIR__ . '/');
define('YESF_TEST_DATA', __DIR__ . '/TestData/');
require(PROJECT_PATH . '/vendor/autoload.php');
$app  = new Yesf(APP_PATH . "/config.ini");