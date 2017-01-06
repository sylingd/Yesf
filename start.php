<?php
//应用所在目录
define("APP_PATH",  __DIR__ . '/application/');
require('framework/Yesf.php');
//初始化
$app  = new yesf\Yesf(APP_PATH . "/Config.ini");
$app->bootstrap()->run();