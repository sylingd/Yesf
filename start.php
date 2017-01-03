<?php
define("APP_PATH",  realpath(__DIR__ . '/../'));
require('framework/Yesf.php');
$app  = new yesf\Yesf(APP_PATH . "/application/config.ini");
$app->bootstrap()->run();