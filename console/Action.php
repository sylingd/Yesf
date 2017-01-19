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
	const HELLO_TEXT = "/******************************************************/\n/*                    Yesf Console                    */\n/******************************************************/";
	const INPUT_TEXT = "What do you want to do?(Enter the serial number): ";
	const BACK_TEXT = "Enter \"back\" to return to the main menu\n";
	public static function getMainText() {
		return self::HELLO_TEXT . "\n1. Show connected servers\n2. Connect to a new server\n3. Reload servers\n4. Get server statistics\n" . self::INPUT_TEXT;
	}
	public static function getMainAction() {
		return [
			1 => 'showServerAction',
			2 => 'connectServerAction',
			3 => 'reloadServerAction',
			4 => 'statServerAction'
		];
	}
	protected static function printServerList() {
		foreach (self::$server as $k => $v) {
			echo $k, '. ', $v->ip, ':', $v->port, "\n";
		}
	}
	public static function showServerAction() {
		echo "\n\nHere is the server list:\n";
		self::printServerList();
		echo "\n\n";
		echo self::BACK_TEXT;
		echo "Enter one or more serial numbers to disconnect (Such as 1,2,3)\nEnter: ";
		$action = trim(fgets(STDIN));
		if ($action === 'back') {
			return;
		}
		$disconnect = explode(',', $action);
		foreach ($disconnect as $k) {
			$k = intval($k);
			unset(self::$server[$k]);
		}
		sleep(1);
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
			sleep(1);
			return;
		}
		self::$server[] = $newConnect;
		echo "Connection succeeded!";
		sleep(1);
	}
	public static function reloadServerAction() {
		echo "\n\nHere is the server list:\n";
		self::printServerList();
		echo "\n\n";
		echo self::BACK_TEXT;
		echo "Enter one or more serial numbers to reload them (Such as 1,2,3)\nEnter:";
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
		self::printServerList();
		echo "\n\n";
		echo self::BACK_TEXT;
		echo "Enter a serial number to see the server statistics\nEnter: ";
		$action = trim(fgets(STDIN));
		if ($action === 'back') {
			return;
		}
		$action = intval($action);
		if (!isset(self::$server[$action])) {
			return;
		}
		$rs = self::$server[$action]->send('getStat');
		echo "Start at: ", date('Y-m-d H:i:s', $rs['start_time']), "\n";
		echo "Connections: ", $rs['connection_num'], "\n";
		echo "Accepted: ", $rs['accept_count'], "\n";
		echo "Closed: ", $rs['close_count'], "\n";
		echo "Tasks queuing: ", $rs['tasking_num'], "\n";
		echo "Request received: ", $rs['request_count'], "\n";
		sleep(1);
	}
}