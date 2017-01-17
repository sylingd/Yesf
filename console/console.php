<?php
/**
 * 控制台
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Tool
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

if (PHP_SAPI !== 'cli') {
	echo 'You must run console at cli mode';
	exit;
}
require('Client.php');
require('Action.php');

//开始
while (TRUE) {
	echo Action::getMainText();
	$actions = Action::getMainAction();
	$action = intval(trim(fgets(STDIN)));
	if (!isset($actions[$action])) {
		echo 'Error! You have entered a wrong serial number!', "\n";
		sleep(1);
		continue;
	}
	call_user_func(['Action', $actions[$action]]);
	sleep(1);
	echo "\n\n\n\n";
}