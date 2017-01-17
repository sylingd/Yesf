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

class Action {
	public static $server = [];
	const HELLO_TEXT = "/************************************************** ****/\n/*                              Yesf Console                              */\n/******************************************************/";
	const INPUT_TEXT = "What do you want to do?(Enter the serial number):";
	public static function getMainText() {
		return self::HELLO_TEXT . "1. Show connected servers\n2. Connect to a new server\n3. Reload servers\n4. Get server statistics\n" . self::INPUT_TEXT;
	}
	public static function getMainAction() {
		return [
			1 => 'showServerAction',
			2 => 'connectServerAction',
			3 => 'reloadServerAction',
			4 => 'statServerAction'
		];
	}
	public static function showServerAction() {
		echo "\n\nHere is the server list:\n";
		foreach ($server as $k => $v) {
			$sockinfo = $v->getsockname();
			echo $k, '. ', $sockinfo['host'], ':', $sockinfo['port'], "\n";
		}
		echo "\n\n";
		echo "Enter \"back\" to return to the main menu\n Enter one or more serial numbers to disconnect (Such as 1,2,3)\nEnter:";
		$action = trim(fgets(STDIN));
		if ($action === 'back') {
			return;
		}
		$disconnect = explode(',', $action);
		foreach ($disconnect as $k) {
			$k = intval($k);
			if (!isset(self::$server[$k])) {
				continue;
			}
			self::$server[$k]->close();
			unset(self::$server[$k]);
		}
	}
	public static function connectServerAction() {
		echo "Please enter the server ip address: ";
		$ip = trim(fgets(STDIN));
		echo "Please enter the server port: ";
		$port = trim(fgets(STDIN));
		echo "\n";
		try {
			$newConnect = new Client($ip, $port);
		} catch (Exception $e) {
			echo "Connection failed!";
			return;
		}
		self::$server[] = $newConnect;
		echo "Connection succeeded!";
	}
	public static function reloadServerAction() {
		echo "\n\nHere is the server list:\n";
		foreach ($server as $k => $v) {
			$sockinfo = $v->getsockname();
			echo $k, '. ', $sockinfo['host'], ':', $sockinfo['port'], "\n";
		}
		echo "\n\n";
		echo "Enter \"back\" to return to the main menu\n Enter one or more serial numbers to reload them (Such as 1,2,3)\nEnter:";
		$action = trim(fgets(STDIN));
		if ($action === 'back') {
			return;
		}
		$reload = explode(',', $action);
		foreach ($reload as $k) {
			$k = intval($k);
			if (!isset(self::$server[$k])) {
				continue;
			}
			self::$server[$k]->send('reload', ['task' => 0]);
		}
	}
	public static function statServerAction() {
		echo "\n\nHere is the server list:\n";
		foreach ($server as $k => $v) {
			$sockinfo = $v->getsockname();
			echo $k, '. ', $sockinfo['host'], ':', $sockinfo['port'], "\n";
		}
		echo "\n\n";
		echo "Enter \"back\" to return to the main menu\n Enter a serial number to see the server statistics\nEnter:";
		$action = trim(fgets(STDIN));
		if ($action === 'back') {
			return;
		}
		$action = intval($action);
		if (!isset(self::$server[$action])) {
			return;
		}
		self::$server[$action]->send('getStat');
		print_r(self::$server[$action]->recv());
	}
}