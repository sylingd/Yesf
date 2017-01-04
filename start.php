<?php
define("APP_PATH",  __DIR__ . '/application/');
require('framework/Yesf.php');
$app  = new yesf\Yesf(APP_PATH . "/config.ini");
$app->bootstrap()->run();