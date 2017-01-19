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
//初始化连接
if (is_file('server.json')) {
	$server = json_decode(file_get_contents('server.json'), 1);
	foreach ($server as $v) {
		try {
			$newConnect = new Client($v['ip'], $v['port']);
		} catch (Exception $e) {
			echo "{$v['ip']}:{$v['port']} Connection failed!";
			return;
		}
		Action::$server[] = $newConnect;
	}
}
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
	echo "\n\n\n\n\n\n";
}