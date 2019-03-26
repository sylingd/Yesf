<?php
/**
 * 日志封装类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Log;
use \yesf\Yesf;
use \Psr\Log\LoggerAwareInterface;

class Logger implements LoggerAwareInterface {
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
	private $logger = NULL;
	public static function should($level) {
		static $level = NULL;
		if ($level === NULL) {
			$level = Yesf::app()->getConfig('logger.level');
			if ($level === 'none') {
				return FALSE;
			}
			$level = ($level && isset(self::LOG_LEVEL[$level])) ? self::LOG_LEVEL[$level] : 3;
		}
		return isset(self::LOG_LEVEL[$type]) && self::LOG_LEVEL[$type] >= $level;
	}
	public static function getLogName() {
		static $name = NULL;
		if ($name === NULL) {
			if (Yesf::app()->getConfig('logger.name')) {
				$name = Yesf::app()->getConfig('logger.name');
			} else {
				$name = Yesf::getProjectConfig('name');
			}
		}
		return $name;
	}
	public function __construct() {
		//$logger
	}
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	public function __call($name, $arguments) {
		if (method_exists($this->logger, $name)) {
			$this->logger->$name(...$arguments);
		}
	}
	public static function __callStatic($name, $arguments) {
		//
	}
}