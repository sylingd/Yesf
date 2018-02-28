<?php
/**
 * 数据库常用操作类
 * 
 * @author ShuangYa
 * @package pkgist
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 */

namespace yesf\library\database;

use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\Swoole;
use \yesf\library\exception\Exception;

class Database {
	private static $db = [];
	/**
	 * 通过读取配置，获取数据库操作类
	 * 也可以自行实例化相应的操作类
	 * 此函数会暂存实例类，使用clear方法清除
	 * 
	 * 类型分为协程（1）和同步（2），当传入0时，则根据当前环境自动判断
	 * 在Task进程中会返回同步类，在非Task进程中返回协程类
	 * 
	 * @access public
	 * @param int $type 类型
	 * @return object(DatabaseInterface)
	 */
	public static function get(int $type = Constant::TYPE_AUTO) {
		if ($type === Constant::TYPE_AUTO) {
			$type = Swoole::$isTaskWorker ? Constant::TYPE_SYNC : Constant::TYPE_CORO;
		}
		if (isset(self::$db[$type])) {
			return self::$db[$type];
		}
		$config = Yesf::app()->getConfig();
		$driver = 'yesf\\library\\database\\' .
			($type === Constant::TYPE_CORO ? 'coroutine' : 'sync') .
			'\\' . ucfirst($config->get('database.type'));
		if (!class_exists($driver)) {
			throw new Exception('Driver ' . ($type === Constant::TYPE_CORO ? 'coroutine' : 'sync') . '/' . ucfirst($config->get('database.type')) . ' not found');
		}
		$config = [
			'host' => $config->get('database.host'),
			'user' => $config->get('database.user'),
			'password' => $config->get('database.password'),
			'database' => $config->get('database.name'),
			'port' => $config->get('database.port')
		];
		self::$db[$type] = new $driver($config);
		return self::$db[$type];
	}
	/**
	 * 清除暂存的实例类
	 * @access public
	 * @param int $type 类型
	 */
	public static function clear(int $type = Constant::TYPE_AUTO) {
		if ($type === Constant::TYPE_AUTO) {
			$type = Swoole::$isTaskWorker ? Constant::TYPE_SYNC : Constant::TYPE_CORO;
		}
		if (isset(self::$db[$type])) {
			unset(self::$db[$type]);
		}
	}
}