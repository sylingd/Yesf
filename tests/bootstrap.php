<?php
use Yesf\Yesf;
define('PROJECT_PATH', realpath(__DIR__ . '/..') . '/');
define('APP_PATH', __DIR__ . '/TestApp/');
define('TEST_SRC', __DIR__ . '/Suite/');
define('TEST_APP', __DIR__ . '/TestApp/');
define('YESF_ROOT', PROJECT_PATH . 'src/');
define('YESF_UNIT', 1);
define('YESF_TEST', __DIR__ . '/');
require(PROJECT_PATH . '/vendor/autoload.php');
$app = new Yesf();

$app->loadEnvConfig(TEST_APP . 'Config/env.ini');
Yesf::getLoader()->addPsr4('YesfTest\\', TEST_SRC);
Yesf::getLoader()->addPsr4('TestApp\\', TEST_APP);