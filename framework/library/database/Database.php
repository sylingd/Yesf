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
use \yesf\library\database\builder\QueryFactory;

class Database {
	private static $db = [];
	private static $custom_driver = [];
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
		$driverType = $config->get('database.type');
		if (isset(self::$custom_driver[$driverType])) {
			//用户自定义driver
			if ($type === Constant::TYPE_CORO && self::$custom_driver[$driverType][1] !== NULL) {
				$driver = self::$custom_driver[$driverType][1];
			} else {
				$driver = self::$custom_driver[$driverType][0];
			}
		} else {
			$driver = 'yesf\\library\\database\\' .
				($type === Constant::TYPE_CORO ? 'coroutine' : 'sync') .
				'\\' . ucfirst($driverType);
			if (!class_exists($driver)) {
				throw new Exception('Driver ' . ($type === Constant::TYPE_CORO ? 'coroutine' : 'sync') . '/' . ucfirst($config->get('database.type')) . ' not found');
			}
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
	/**
	 * 获取Builder实例类
	 * @access public
	 * @param string $type
	 * @return object(QueryFactory)
	 */
	public static function getBuilder($type = NULL) {
		static $builders = [];
		if ($type === NULL) {
			$type = Yesf::app()->getConfig('database.type');
		}
		if (!isset($builders[$type])) {
			$builders[$type] = new QueryFactory($type);
		}
		return $builders[$type];
	}
	/**
	 * 注册自定义driver
	 * @access public
	 * @param string $type
	 * @param string $sync_class 同步类名
	 * @param string $coro_class 协程类名
	 */
	public static function registerDriver(string $type, $sync_class, $coro_class = NULL) {
		if (!class_exists($sync_class)) {
			throw new Exception('Sync driver ' . $type . ' not found');
		}
		if ($coro_class !== NULL && !class_exists($coro_class)) {
			throw new Exception('Coroutine driver ' . $type . ' not found');
		}
		self::$custom_driver[$type] = [$sync_class, $coro_class];
	}
}