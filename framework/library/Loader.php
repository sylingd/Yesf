<?php
/**
 * 自动加载相关类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;

use \yesf\Yesf;

class Loader {
	/**
	 * 不属于框架的一些namespace
	 * 用于自动加载
	 */
	protected static $namespace = [];
	/**
	 * Autoload
	 */
	public static function autoload($className) {
		//解析namespace名
		if (strpos($className, 'yesf\\') === 0) {
			//框架类
			$fileName = substr($className, 4) . '.php';
			$fileName = YESF_ROOT . str_replace('\\', '/', $fileName);
		} elseif (Yesf::app()->getConfig()->has('application.class.' . $className)) {
			$fileName = Yesf::app()->getConfig('application.dir') . Yesf::app()->getConfig('application.class.' . $className);
		} else {
			//可能是应用自身注册的namespace
			foreach (self::$namespace as $k => $v) {
				if (strpos($className, $k) === 0) {
					$fileName = $v . str_replace('\\', '/', substr($className, strlen($k))) . '.php';
					break;
				}
			}
		}
		if (isset($fileName) && is_file($fileName)) {
			require($fileName);
		}
	}
	/**
	 * 注册一个namespace，用于自动加载
	 * 此namespace不需要完全匹配，即yesf\library\event\这样的namespace可以被yesf\library\匹配到
	 * @param string $namespace
	 * @param string $dir 从何目录下加载，默认为应用目录下的library
	 */
	public static function registerNamespace($namespace, $dir = NULL) {
		if (substr($namespace, -1, 1) !== '\\') {
			$namespace .= '\\';
		}
		self::$namespace[$namespace] = $dir;
	}
	/**
	 * 注册自动加载
	 */
	public static function register() {
		//注册自动加载
		spl_autoload_register('yesf\\library\\Loader::autoload', TRUE, TRUE);
	}
}