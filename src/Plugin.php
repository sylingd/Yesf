<?php
/**
 * 插件主类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace Yesf;

class Plugin {
	protected static $plugins = [];
	/**
	 * 注册一个插件
	 * 
	 * @access public
	 * @param string $event 事件名称
	 * @param callable $callback 回调函数
	 */
	public static function register(string $event, callable $callback) {
		if (!isset(self::$plugins[$event])) {
			self::$plugins[$event] = [];
		}
		self::$plugins[$event][] = $callback;
	}
	/**
	 * 清除已注册的插件
	 * 
	 * @access public
	 * @param string $event 事件名称
	 */
	public static function clear(string $event = '') {
		if (empty($event)) {
			self::$plugins = [];
		} else {
			self::$plugins[$event] = [];
		}
	}
	/**
	 * 触发一个事件
	 * 
	 * @access public
	 * @param string $event 事件名称
	 * @param array $data 参数
	 */
	public static function trigger(string $event, $data = []) {
		$result = null;
		if (isset(self::$plugins[$event])) {
			foreach (self::$plugins[$event] as $callback) {
				$result = $callback(...$data);
				if ($result !== null) {
					break;
				}
			}
		}
		return $result;
	}
}
