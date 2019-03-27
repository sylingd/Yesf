<?php
/**
 * 数据库常用操作类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Relational Database
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 */

namespace Yesf\Database;

use Yesf\Yesf;
use Yesf\Config;
use Yesf\Swoole;
use Yesf\Exception\Exception;
use Yesf\Database\Builder\QueryFactory;

class Database {
	private static $db = [];
	private static $custom_driver = [];
	private static $pool_config = [];
	private static $default_type = NULL;
	/**
	 * 通过读取配置，获取数据库操作类
	 * 
	 * @access public
	 * @param string $type 类型
	 * @return object(DatabaseAbstract)
	 */
	public static function get($type = NULL) {
		$config = Yesf::app()->getConfig();
		if ($type === NULL) {
			$type = self::$default_type;
		}
		if (isset(self::$db[$type])) {
			return self::$db[$type];
		}
		if (isset(self::$custom_driver[$type])) {
			//用户自定义driver
			$driver = self::$custom_driver[$type];
		} else {
			$driver = 'Yesf\\Driver\\' . ucfirst($type);
			if (!class_exists($driver)) {
				throw new Exception('Driver ' . ucfirst($config->get('database.type')) . ' not found');
			}
		}
		if ($type === self::$default_type) {
			$config = [
				'host' => $config->get('database.host'),
				'user' => $config->get('database.user'),
				'password' => $config->get('database.password'),
				'database' => $config->get('database.name'),
				'port' => $config->get('database.port')
			];
		} else {
			$config = [
				'host' => $config->get("database.{$type}.host"),
				'user' => $config->get("database.{$type}.user"),
				'password' => $config->get("database.{$type}.password"),
				'database' => $config->get("database.{$type}.name"),
				'port' => $config->get("database.{$type}.port")
			];
		}
		self::$db[$type] = new $driver($config);
		return self::$db[$type];
	}
	/**
	 * 获取Builder实例类
	 * 
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
	 * 
	 * @access public
	 * @param string $type
	 * @param string $clazz 类名
	 */
	public static function registerDriver(string $type, $clazz = NULL) {
		if (!class_exists($clazz)) {
			throw new Exception('Driver ' . $type . ' not found');
		}
		self::$custom_driver[$type] = $clazz;
	}
}