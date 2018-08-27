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
	/**
	 * 记录日志主函数
	 * @access public
	 * @param string $type 日志类型
	 * @param string $message 日志内容
	 */
	public static function log(string $type, string $message) {
		static $logger = NULL;
		if (!class_exists('\\SeasLog')) {
			return;
		}
		//初始化
		$level = Yesf::app()->getConfig('logger.level');
		if ($level === 'none') {
			return;
		}
		$log_level = ($level && isset(self::LOG_LEVEL[$level])) ? self::LOG_LEVEL[$level] : 3;
		if ($logger === NULL) {
			if (Yesf::app()->getConfig('logger.path')) {
				SeasLog::setBasePath(Yesf::app()->getConfig('logger.path'));
			}
			if (Yesf::app()->getConfig('logger.name')) {
				SeasLog::setLogger(Yesf::app()->getConfig('logger.name'));
			} else {
				SeasLog::setLogger(Yesf::app()->getConfig('application.name'));
			}
			$logger = SeasLog::getLastLogger();
		}
		//判断是否应该记录
		//不使用SeasLog自带的判断，是为了方便程序后期进行修改
		if (!isset(self::LOG_LEVEL[$type]) || self::LOG_LEVEL[$type] < $log_level) {
			return;
		}
		//获取SeasLog的常量
		$type = constant('SEASLOG_' . strtoupper($type));
		$logger->log($type, $message);
	}
	/**
	 * 以下为各个级别的封装
	 * 方便调用
	 * @access public
	 * @param string $message
	 */
	public static function debug($message) {
		self::log(__METHOD__, $message);
	}
	public static function info($message) {
		self::log(__METHOD__, $message);
	}
	public static function notice($message) {
		self::log(__METHOD__, $message);
	}
	public static function warning($message) {
		self::log(__METHOD__, $message);
	}
	public static function error($message) {
		self::log(__METHOD__, $message);
	}
	public static function critical($message) {
		self::log(__METHOD__, $message);
	}
	public static function alert($message) {
		self::log(__METHOD__, $message);
	}
	public static function emergency($message) {
		self::log(__METHOD__, $message);
	}
}