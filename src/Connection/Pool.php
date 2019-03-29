<?php
/**
 * 连接池管理类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Connection;

use Yesf\Yesf;
use Yesf\Exception\ConnectionException;
use Yesf\Exception\InvalidClassException;

class Pool {
	protected static $connection_default;
	protected static $pool = [];
	protected static $driver = [];
	protected static $adapter = [];
	/**
	 * init阶段，读取基本配置
	 * 
	 * @access public
	 */
	public static function init() {
		$c = Yesf::app()->getConfig('connection.default');
		if ($c === null) {
			self::$connection_default = [
				'min' => 1,
				'max' => 3,
			];
		} else {
			self::$connection_default = [
				'min' => isset($c['min']) ? intval($c['min']) : 1,
				'max' => isset($c['max']) ? intval($c['max']) : 1,
			];
		}
		// 注册默认Driver和Adapter
		self::setDriver('mysql', \Yesf\Connection\Driver\Mysql::class);
		self::setDriver('redis', \Yesf\Connection\Driver\Redis::class);
		self::setAdapter('mysql', \Yesf\RD\Adapter\Mysql::class);
		self::setAdapter('redis', \Yesf\Cache\Adapter\Redis::class);
	}
	public static function getMin() {
		return self::$connection_default['min'];
	}
	public static function getMax() {
		return self::$connection_default['max'];
	}
	/**
	 * Get a connection
	 * 
	 * @access public
	 * @param mixed $config
	 */
	public static function get($config = null) {
		if (!isset($config['driver'])) {
			throw new ConnectionException("Unknown driver");
		}
		if (!isset($config['host']) || !isset($config['port'])) {
			throw new ConnectionException("Host and Port is required");
		}
		$type = $config['driver'];
		$hash = md5($type . ':' . $config['host'] . ':' . $config['port']);
		if (!isset(self::$pool[$hash])) {
			if (isset(self::$driver[$type])) {
				$className = self::$driver[$type];
			} else {
				$className = __NAMESPACE__ . '\\Driver\\' . ucfirst($type);
			}
			$instance = new $className($config);
			self::$pool[$hash] = $instance;
			return $instance;
		} else {
			return self::$pool[$hash];
		}
	}
	/**
	 * Get a connection with Adapter
	 * If $config is string, this function will get config from Yesf::app()->getConfig
	 * If $config is array, this function will use it directly
	 * 
	 * @access public
	 * @param mixed $config
	 */
	public static function getAdapter($config = null) {
		if (is_string($config)) {
			$config = Yesf::app()->getConfig('connection.' . $config);
		}
		if (!isset($config['adapter'])) {
			throw new ConnectionException("Unknown adapter");
		}
		$type = $config['adapter'];
		// Get connection
		$connection = Pool::get($config);
		$hash = spl_object_hash($connection);
		if (!isset(self::$db[$hash])) {
			if (isset(self::$adapter[$type])) {
				$className = self::$adapter[$type];
			} else {
				throw new ConnectionException("Unknown adapter");
			}
			$instance = new $className($connection);
			self::$db[$hash] = $instance;
			return $instance;
		} else {
			return self::$db[$hash];
		}
	}
	public static function getRD() {
		$default = Yesf::app()->getConfig('database');
		return self::getAdapter($default);
	}
	public static function getCache() {
		$default = Yesf::app()->getConfig('cache');
		return self::getAdapter($default);
	}
	/**
	 * 注册自定义Driver
	 * 
	 * @access public
	 * @param string $type
	 * @param string $className 类名
	 */
	public static function setDriver($type, $className) {
		if (!is_subclass_of($className, PoolInterface::class)) {
			throw new InvalidClassException("Class $className not implement PoolInterface");
		}
		self::$driver[$type] = $className;
	}
	/**
	 * 注册自定义Adapter
	 * 
	 * @access public
	 * @param string $type
	 * @param string $className 类名
	 */
	public static function setAdapter($type, $className) {
		self::$adapter[$type] = $className;
	}
}