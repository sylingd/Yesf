<?php
//应用所在牡蛎
define("APP_PATH",  __DIR__ . '/application/');
require('framework/Yesf.php');
//初始化
$app  = new yesf\Yesf(APP_PATH . "/config.ini");
$app->bootstrap()->run();