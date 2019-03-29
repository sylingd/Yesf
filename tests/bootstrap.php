<?php
use Yesf\Yesf;
define('PROJECT_PATH', realpath(__DIR__ . '/..') . '/');
define('APP_PATH', __DIR__ . '/TestData/');
define('TEST_SRC', __DIR__ . '/Suite/');
define('YESF_ROOT', PROJECT_PATH . 'src/');
define('YESF_UNIT', 1);
define('YESF_TEST', __DIR__ . '/');
define('YESF_TEST_DATA', __DIR__ . '/TestData/');
require(PROJECT_PATH . '/vendor/autoload.php');
$app = new Yesf();

Yesf::getLoader()->addPsr4('YesfTest\\', TEST_SRC);
Yesf::getLoader()->addPsr4('TestApp\\', YESF_TEST_DATA);