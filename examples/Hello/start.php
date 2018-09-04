<?php
//应用所在目录
define("APP_PATH",  __DIR__ . '/application/');
//使用Composer
require(__DIR__ . '/vendor/autoload.php');
//初始化
$app = new yesf\Yesf();
$app->bootstrap()->run(APP_PATH . "/config/env.ini");