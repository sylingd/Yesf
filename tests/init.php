<?php
use yesf\Yesf;
use yesf\library\Loader;
define('APP_PATH', realpath(__DIR__ . '/../application/') . '/');
define('YESF_ROOT', realpath(__DIR__ . '/../framework/') . '/');
define('YESF_UNIT', 1);
define('YESF_TEST', __DIR__ . '/');
define('YESF_TEST_DATA', __DIR__ . '/TestData/');
require(YESF_ROOT . 'Yesf.php');
$app  = new Yesf(APP_PATH . "/config.ini");
if (!class_exists('yesf\\library\\Loader', FALSE)) {
	require(YESF_ROOT . 'library/Loader.php');
}
Loader::register();