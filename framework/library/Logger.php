<?php
/**
 * 基于SeasLog的日志封装类
 * 如果不存在SeasLog，则不会有任何效果，程序照常运行
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;
use \yesf\Yesf;
use \SeasLog;

class Logger {
	const LOG_LEVEL = [
		'debug' => 0,
		'info' => 1,
		'notice' => 2,
		'warning' => 3,
		'error' => 4,
		'critical' => 5,
		'alert' => 6,
		'emergency' => 7
	];
	protected static $log_level = NULL;
	protected static $logger = NULL;
	/**
	 * Init
	 */
	public static function init() {
		if (!class_exists('\\SeasLog')) {
			return;
		}
		$level = Yesf::app()->getConfig('logger.level');
		if ($level === 'none') {
			return;
		}
		self::$log_level = ($level && isset(self::LOG_LEVEL[$level])) ? self::LOG_LEVEL[$level] : 3;
		if (self::$logger === NULL) {
			if (Yesf::app()->getConfig('logger.path')) {
				SeasLog::setBasePath(Yesf::app()->getConfig('logger.path'));
			}
			if (Yesf::app()->getConfig('logger.name')) {
				self::$logger = Yesf::app()->getConfig('logger.name');
			} else {
				self::$logger = Yesf::getProjectConfig('name');
			}
			SeasLog::setLogger(self::$logger);
		}
	}
	/**
	 * 记录日志主函数
	 * @access public
	 * @param string $type 日志类型
	 * @param string $message 日志内容
	 */
	public static function log(string $type, string $message) {
		//判断是否应该记录
		//不使用SeasLog自带的判断，方便程序动态进行修改
		if (!isset(self::LOG_LEVEL[$type]) || self::LOG_LEVEL[$type] < self::$log_level) {
			return;
		}
		//获取SeasLog的常量
		$type = constant('SEASLOG_' . strtoupper($type));
		SeasLog::log($type, $message, [], self::$logger);
	}
	/**
	 * 以下为各个级别的封装
	 * 方便调用
	 * @access public
	 * @param string $message
	 */
	public static function debug($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function info($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function notice($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function warning($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function error($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function critical($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function alert($message) {
		self::log(__FUNCTION__, $message);
	}
	public static function emergency($message) {
		self::log(__FUNCTION__, $message);
	}
}