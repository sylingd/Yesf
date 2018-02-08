<?php
use yesf\Yesf;
use yesf\library\Loader;
define('PROJECT_PATH', realpath(__DIR__ . '/..') . '/');
define('APP_PATH', PROJECT_PATH . 'application/');
define('YESF_ROOT', PROJECT_PATH . 'framework/');
define('YESF_UNIT', 1);
define('YESF_TEST', __DIR__ . '/');
define('YESF_TEST_DATA', __DIR__ . '/TestData/');
require(YESF_ROOT . 'Yesf.php');
require(PROJECT_PATH . '/vendor/autoload.php');
$app  = new Yesf(APP_PATH . "/config.ini");
if (!class_exists('yesf\\library\\Loader', FALSE)) {
	require(YESF_ROOT . 'library/Loader.php');
}
Loader::register();