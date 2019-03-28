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

class Pool {
	public static $config;
	private static $pool = [];
	/**
	 * init阶段，读取基本配置
	 * 
	 * @access public
	 */
	public static function init() {
		$config = Yesf::app()->getConfig();
		$c = $config->get('pool');
		foreach ($c as $k => $v) {
			self::$config[$k] = [
				'min' => isset($v['min']) ? intval($v['min']) : 1,
				'max' => isset($v['max']) ? intval($v['max']) : 1,
			];
		}
		if (!isset(self::$config['default'])) {
			self::$config['default'] = [
				'min' => 1,
				'max' => 3,
			];
		}
	}
	public static function getMin($name) {
		return isset(self::$config[$name]) ? self::$config[$name]['min'] : self::$config['default']['min'];
	}
	public static function getMax($name) {
		return isset(self::$config[$name]) ? self::$config[$name]['max'] : self::$config['default']['max'];
	}
	public static function get($type, $config = null) {
		$className = __NAMESPACE__ . '\\Driver\\' . ucfirst($type);
		if ($config === null) {
			$config = Yesf::app()->getConfig('connection.' . $type);
		}
		if (!isset(self::$pool[$type])) {
			self::$pool[$type] = [];
		}
		if (!isset($config['host']) || !isset($config['port'])) {
			throw new ConnectionException("Host and Port is required");
		}
		$hash = md5($config['host'] . ':' . $config['port']);
		if (!isset(self::$pool[$type][$hash])) {
			$instance = new $className($config);
			self::$pool[$type][$hash] = $instance;
			return $instance;
		} else {
			return self::$pool[$type][$hash];
		}
	}
}