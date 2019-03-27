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

class Pool {
	public static $config;
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
	public function get($config = null) {
		//
		self::$default_type = $config->get('database.type');
	}
}